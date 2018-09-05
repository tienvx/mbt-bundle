<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\QueuedLoop;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Helper\PathBuilder;
use Tienvx\Bundle\MbtBundle\Message\QueuedLoopMessage;
use Tienvx\Bundle\MbtBundle\Helper\PathRunner;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class QueuedLoopPathReducer extends AbstractPathReducer
{
    /**
     * @var MessageBusInterface
     */
    protected $messageBus;

    public function __construct(
        MessageBusInterface $messageBus,
        EventDispatcherInterface $dispatcher,
        Registry $workflowRegistry,
        SubjectManager $subjectManager,
        EntityManagerInterface $entityManager)
    {
        parent::__construct($dispatcher, $workflowRegistry, $subjectManager, $entityManager);
        $this->messageBus = $messageBus;
    }

    /**
     * @param Bug $bug
     * @throws \Exception
     */
    public function reduce(Bug $bug)
    {
        $queuedLoop = new QueuedLoop();
        $queuedLoop->setMessageHashes([]);
        $queuedLoop->setIndicator($bug->getLength());
        $queuedLoop->setBug($bug);

        $this->entityManager->persist($queuedLoop);
        $this->entityManager->flush();

        $this->dispatch($queuedLoop->getId());
    }

    /**
     * @param string $message
     * @throws \Exception
     */
    public function handle(string $message)
    {
        $queuedLoopMessage = QueuedLoopMessage::fromString($message);
        $queuedLoop = $this->entityManager->getRepository(QueuedLoop::class)->find($queuedLoopMessage->getBugId());
        $path = PathBuilder::build($queuedLoop->getBug()->getPath());
        $model = $queuedLoop->getBug()->getTask()->getModel();

        if (!$queuedLoop || !$queuedLoop instanceof QueuedLoop) {
            return;
        }

        if ($queuedLoop->getBug()->getLength() >= $queuedLoopMessage->getLength()) {
            // The reproduce path has not been reduced.
            list($i, $j) = $queuedLoopMessage->getPair();
            if ($j <= $path->countTransitions() && !array_diff($path->getPlacesAt($i), $path->getPlacesAt($j))) {
                $newPath = PathBuilder::createWithoutLoop($path, $i, $j);
                // Make sure new path shorter than old path.
                if ($newPath->countTransitions() < $path->countTransitions()) {
                    try {
                        $subject = $this->subjectManager->createSubjectForModel($model);
                        $workflow = $this->workflowRegistry->get($subject, $model);
                        PathRunner::run($newPath, $workflow, $subject);
                    } catch (Throwable $newThrowable) {
                        if ($newThrowable->getMessage() === $queuedLoop->getBug()->getBugMessage()) {
                            $updated = $this->updatePath($queuedLoop->getBug(), $newPath, $newPath->countTransitions());
                            if ($updated) {
                                $this->dispatch($queuedLoop->getId());
                            }
                        }
                    }
                }
            }
        }

        $this->postHandle($queuedLoopMessage);
    }

    /**
     * @param QueuedLoopMessage $queuedLoopMessage
     * @throws \Exception
     */
    public function postHandle(QueuedLoopMessage $queuedLoopMessage)
    {
        $this->entityManager->beginTransaction();
        try {
            $queuedLoop = $this->entityManager->find(QueuedLoop::class, $queuedLoopMessage->getBugId(), LockMode::PESSIMISTIC_WRITE);

            if (!$queuedLoop || !$queuedLoop instanceof QueuedLoop) {
                return;
            }

            $messageHashes = $queuedLoop->getMessageHashes();
            $hash = sha1($queuedLoopMessage);
            if (($key = array_search($hash, $messageHashes)) !== false) {
                unset($messageHashes[$key]);
            }
            $queuedLoop->setMessageHashes($messageHashes);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Throwable $throwable) {
            // Something happen, ignoring.
            $this->entityManager->rollBack();
        }

        if (isset($queuedLoop) && empty($queuedLoop->getMessageHashes())) {
            if ($queuedLoop->getIndicator() > 0) {
                $this->dispatch($queuedLoop->getId());
            }
            else {
                // All messages has been handled.
                $this->entityManager->remove($queuedLoop);
                $this->entityManager->flush();
                $this->finish($queuedLoop->getBug()->getId());
            }
        }
    }

    /**
     * @param int $queuedLoopId
     * @throws \Exception
     */
    public function dispatch(int $queuedLoopId)
    {
        $this->entityManager->beginTransaction();
        try {
            $queuedLoop = $this->entityManager->find(QueuedLoop::class, $queuedLoopId, LockMode::PESSIMISTIC_WRITE);

            if (!$queuedLoop || !$queuedLoop instanceof QueuedLoop) {
                return;
            }

            $path = unserialize($queuedLoop->getBug()->getPath());

            if (!$path instanceof Path) {
                throw new Exception(sprintf('Path must be instance of %s', Path::class));
            }

            $distance = $queuedLoop->getIndicator();
            $pairs = [];
            while ($distance > 0 && empty($pairs)) {
                for ($i = 0; $i < $path->countTransitions(); $i++) {
                    $j = $i + $distance;
                    if ($j <= $path->countTransitions() && !array_diff($path->getPlacesAt($i), $path->getPlacesAt($j))) {
                        $pairs[] = [$i, $j];
                    }
                }
                $distance--;
            }

            $messageHashes = [];
            foreach ($pairs as $pair) {
                $message = new QueuedLoopMessage($queuedLoop->getId(), $path->countEdges(), $pair);
                $this->messageBus->dispatch($message);
                $messageHashes[] = sha1($message);
            }

            $queuedLoop->setIndicator($distance);
            $queuedLoop->setMessageHashes(array_unique($messageHashes));
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Throwable $throwable) {
            // Something happen, ignoring.
            $this->entityManager->rollBack();
        }
    }

    /**
     * {@inheritdoc}
     * @return bool
     */
    protected function updatePath(Bug $bug, string $path, int $length): bool
    {
        $updated = false;
        $this->entityManager->beginTransaction();
        try {
            $bug = $this->entityManager->find(Bug::class, $bug->getId(), LockMode::PESSIMISTIC_WRITE);

            if (!$bug || !$bug instanceof Bug) {
                return $updated;
            }

            $bug->setPath($path);
            $bug->setLength($length);
            $this->entityManager->flush();
            $this->entityManager->commit();

            $updated = true;
        } catch (Throwable $throwable) {
            $this->entityManager->rollBack();
        } finally {
            return $updated;
        }
    }

    public static function getName()
    {
        return 'queued-loop';
    }
}
