<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Path;
use Tienvx\Bundle\MbtBundle\Helper\PathBuilder;
use Tienvx\Bundle\MbtBundle\Helper\PathRunner;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\FinishReducePathMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

abstract class AbstractPathReducer implements PathReducerInterface
{
    /**
     * @var Registry
     */
    protected $workflowRegistry;

    /**
     * @var SubjectManager
     */
    protected $subjectManager;

    /**
     * @var MessageBusInterface
     */
    protected $messageBus;

    /**
     * @var GraphBuilder
     */
    protected $graphBuilder;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        Registry $workflowRegistry,
        SubjectManager $subjectManager,
        MessageBusInterface $messageBus,
        GraphBuilder $graphBuilder,
        EntityManagerInterface $entityManager
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->subjectManager = $subjectManager;
        $this->messageBus = $messageBus;
        $this->graphBuilder = $graphBuilder;
        $this->entityManager = $entityManager;
    }

    public static function support(): bool
    {
        return true;
    }

    /**
     * @param Bug      $bug
     * @param Workflow $workflow
     * @param int      $length
     * @param int      $from
     * @param int      $to
     *
     * @throws Exception
     * @throws Throwable
     * @throws InvalidArgumentException
     */
    public function handle(Bug $bug, Workflow $workflow, int $length, int $from, int $to)
    {
        $model = $bug->getTask()->getModel()->getName();
        $graph = $this->graphBuilder->build($workflow);
        $path = $bug->getPath();

        if ($path->getLength() === $length) {
            // The reproduce path has not been reduced.
            $newPath = PathBuilder::createWithShortestPath($graph, $path, $from, $to);
            // Make sure new path shorter than old path.
            if ($newPath->getLength() < $path->getLength()) {
                try {
                    $subject = $this->subjectManager->createSubject($model);
                    PathRunner::run($newPath, $workflow, $subject);
                } catch (Throwable $newThrowable) {
                    if ($newThrowable->getMessage() === $bug->getBugMessage()) {
                        $this->updatePath($bug, $newPath);
                    }
                }
            }
        }

        $this->messageBus->dispatch(new FinishReducePathMessage($bug->getId()));
    }

    /**
     * @param Bug  $bug
     * @param Path $newPath
     *
     * @throws Throwable
     */
    protected function updatePath(Bug $bug, Path $newPath)
    {
        $callback = function () use ($bug, $newPath) {
            $this->entityManager->lock($bug, LockMode::PESSIMISTIC_WRITE);

            $bug->setPath($newPath);
        };

        $this->entityManager->transactional($callback);

        $this->messageBus->dispatch(new ReduceBugMessage($bug->getId(), static::getName()));
    }
}
