<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Helper\PathBuilder;
use Tienvx\Bundle\MbtBundle\Helper\PathRunner;
use Tienvx\Bundle\MbtBundle\Message\ReductionMessage;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class LoopPathReducer extends AbstractPathReducer
{
    /**
     * @var MessageBusInterface
     */
    protected $messageBus;

    public function __construct(
        MessageBusInterface $messageBus,
        EventDispatcherInterface $dispatcher,
        SubjectManager $subjectManager,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($dispatcher, $subjectManager, $entityManager);
        $this->messageBus = $messageBus;
    }

    /**
     * @param Bug $bug
     * @throws Exception
     */
    public function reduce(Bug $bug)
    {
        parent::reduce($bug);

        $this->dispatch($bug->getId(), $bug->getLength());
    }

    /**
     * @param ReductionMessage $message
     * @throws Exception
     */
    public function handle(ReductionMessage $message)
    {
        $bug = $this->entityManager->find(Bug::class, $message->getBugId());

        if (!$bug || !$bug instanceof Bug) {
            return;
        }

        $path = PathBuilder::build($bug->getPath());
        $model = $bug->getTask()->getModel();

        if ($bug->getLength() >= $message->getData()['length']) {
            // The reproduce path has not been reduced.
            list($i, $j) = $message->getData()['pair'];
            if ($j <= $path->countPlaces() && !array_diff($path->getPlacesAt($i), $path->getPlacesAt($j))) {
                $newPath = PathBuilder::createWithoutLoop($path, $i, $j);
                // Make sure new path shorter than old path.
                if ($newPath->countPlaces() < $path->countPlaces()) {
                    try {
                        $subject = $this->subjectManager->createSubject($model);
                        $workflow = $this->workflowRegistry->get($subject, $model);
                        PathRunner::run($newPath, $workflow, $subject);
                    } catch (Throwable $newThrowable) {
                        if ($newThrowable->getMessage() === $bug->getBugMessage()) {
                            $this->dispatch($bug->getId(), $newPath->countPlaces(), $newPath);
                        }
                    }
                }
            }
        }

        $this->postHandle($message);
    }

    /**
     * @param ReductionMessage $message
     * @throws Exception
     */
    public function postHandle(ReductionMessage $message)
    {
        $this->entityManager->beginTransaction();
        try {
            $bug = $this->entityManager->find(Bug::class, $message->getBugId(), LockMode::PESSIMISTIC_WRITE);

            if (!$bug || !$bug instanceof Bug) {
                return;
            }

            $bug->setMessagesCount($bug->getMessagesCount() - 1);
            $this->entityManager->flush();
            $this->entityManager->commit();

            if ($bug->getMessagesCount() === 0) {
                if ($message->getData()['distance'] > 1) {
                    $this->dispatch($bug->getId(), $message->getData()['distance'] - 1);
                } else {
                    $this->finish($bug);
                }
            }
        } catch (Throwable $throwable) {
            // Something happen, ignoring.
            $this->entityManager->rollBack();
        }
    }

    /**
     * @param int $bugId
     * @param int $distance
     * @param Path|null $newPath
     * @throws Exception
     */
    public function dispatch(int $bugId, int $distance, Path $newPath = null)
    {
        $this->entityManager->beginTransaction();
        try {
            $bug = $this->entityManager->find(Bug::class, $bugId, LockMode::PESSIMISTIC_WRITE);

            if (!$bug || !$bug instanceof Bug) {
                return;
            }

            $path = unserialize($bug->getPath());

            if (!$path instanceof Path) {
                throw new Exception(sprintf('Path must be instance of %s', Path::class));
            }

            $pairs = [];
            while ($distance > 0 && empty($pairs)) {
                for ($i = 0; $i < $path->countPlaces(); $i++) {
                    $j = $i + $distance;
                    if ($j < $path->countPlaces() && !array_diff($path->getPlacesAt($i), $path->getPlacesAt($j))) {
                        $pair = [$i, $j];
                        $message = new ReductionMessage($bug->getId(), static::getName(), [
                            'length' => $path->countPlaces(),
                            'pair' => $pair,
                            'distance' => $distance,
                        ]);
                        $this->messageBus->dispatch($message);
                        $pairs[] = $pair;
                    }
                }
                $distance--;
            }

            $bug->setMessagesCount(count($pairs));
            if ($newPath) {
                $bug->setPath(serialize($newPath));
                $bug->setLength($newPath->countPlaces());
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Throwable $throwable) {
            // Something happen, ignoring.
            $this->entityManager->rollBack();
        }
    }

    public static function getName()
    {
        return 'loop';
    }
}
