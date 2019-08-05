<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Path;
use Tienvx\Bundle\MbtBundle\Helper\PathBuilder;
use Tienvx\Bundle\MbtBundle\Helper\PathRunner;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\CaptureScreenshotsMessage;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Message\UpdateBugStatusMessage;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

abstract class AbstractPathReducer implements PathReducerInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var Registry
     */
    protected $workflowRegistry;

    /**
     * @var SubjectManager
     */
    protected $subjectManager;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var MessageBusInterface
     */
    protected $messageBus;

    /**
     * @var GraphBuilder
     */
    protected $graphBuilder;

    public function __construct(
        Registry $workflowRegistry,
        EventDispatcherInterface $dispatcher,
        SubjectManager $subjectManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        GraphBuilder $graphBuilder
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->dispatcher = $dispatcher;
        $this->subjectManager = $subjectManager;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->graphBuilder = $graphBuilder;
    }

    public static function support(): bool
    {
        return true;
    }

    protected function finish(Bug $bug)
    {
        if (!empty($bug->getTask()->getReporters())) {
            foreach ($bug->getTask()->getReporters() as $reporter) {
                $this->messageBus->dispatch(new ReportBugMessage($bug->getId(), $reporter->getName()));
            }
            $this->messageBus->dispatch(new UpdateBugStatusMessage($bug->getId(), 'reported'));
        } else {
            $this->messageBus->dispatch(new UpdateBugStatusMessage($bug->getId(), 'reduced'));
        }
        if ($bug->getTask()->getTakeScreenshots()) {
            $this->messageBus->dispatch(new CaptureScreenshotsMessage($bug->getId()));
        }
    }

    /**
     * @param int $bugId
     * @param int $length
     * @param int $from
     * @param int $to
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function handle(int $bugId, int $length, int $from, int $to)
    {
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug || !$bug instanceof Bug) {
            return;
        }

        $model = $bug->getTask()->getModel()->getName();
        $workflow = WorkflowHelper::get($this->workflowRegistry, $model);

        $graph = $this->graphBuilder->build($workflow);
        $path = $bug->getPath();

        if ($bug->getLength() >= $length) {
            // The reproduce path has not been reduced.
            $newPath = PathBuilder::createWithShortestPath($graph, $path, $from, $to);
            // Make sure new path shorter than old path.
            if ($newPath->countPlaces() < $path->countPlaces()) {
                try {
                    $subject = $this->subjectManager->createSubject($model);
                    PathRunner::run($newPath, $workflow, $subject);
                } catch (Throwable $newThrowable) {
                    if ($newThrowable->getMessage() === $bug->getBugMessage()) {
                        $this->dispatch($bug->getId(), $newPath);
                    }
                }
            }
        }

        $this->postHandle($bugId);
    }

    /**
     * @param Bug $bug
     *
     * @throws Exception
     */
    public function reduce(Bug $bug)
    {
        $messagesCount = $this->dispatch($bug->getId());
        if (0 === $messagesCount) {
            $this->finish($bug);
        }
    }

    /**
     * @param int $bugId
     *
     * @throws Exception
     */
    public function postHandle(int $bugId)
    {
        $callback = function () use ($bugId) {
            $bug = $this->entityManager->find(Bug::class, $bugId, LockMode::PESSIMISTIC_WRITE);

            if ($bug instanceof Bug && $bug->getMessagesCount() > 0) {
                $bug->setMessagesCount($bug->getMessagesCount() - 1);
            }

            return $bug;
        };

        $bug = $this->entityManager->transactional($callback);
        if ($bug instanceof Bug && 0 === $bug->getMessagesCount()) {
            $this->finish($bug);
        }
    }

    protected function getBug(int $bugId, Path $newPath = null)
    {
        $bug = $this->entityManager->find(Bug::class, $bugId, LockMode::PESSIMISTIC_WRITE);

        if ($bug instanceof Bug && $newPath) {
            $bug->setPath($newPath);
            $bug->setLength($newPath->countPlaces());
        }

        return $bug;
    }
}
