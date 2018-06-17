<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\QueuedReproducePath;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Message\QueuedLoopMessage;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\PathRunner;

class QueuedLoopPathReducer extends AbstractPathReducer
{
    protected $messageBus;
    protected $entityManager;

    public function __construct(
        PathRunner $runner,
        EventDispatcherInterface $dispatcher,
        MessageBusInterface $messageBus,
        ModelRegistry $modelRegistry,
        GraphBuilder $graphBuilder,
        EntityManagerInterface $entityManager)
    {
        parent::__construct($runner, $dispatcher, $modelRegistry, $graphBuilder);
        $this->messageBus = $messageBus;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ReproducePath $reproducePath
     * @throws \Exception
     */
    public function reduce(ReproducePath $reproducePath)
    {
        $queuedReproducePath = new QueuedReproducePath();
        $queuedReproducePath->setMessageHashes([]);
        $queuedReproducePath->setIndicator($reproducePath->getLength());
        $queuedReproducePath->setReproducePath($reproducePath);

        $this->entityManager->persist($queuedReproducePath);
        $this->entityManager->flush();

        $this->dispatch($queuedReproducePath->getId());
    }

    /**
     * @param string $message
     * @throws \Exception
     */
    public function handle(string $message)
    {
        $queuedLoopMessage = QueuedLoopMessage::fromString($message);
        $queuedReproducePath = $this->entityManager->getRepository(ReproducePath::class)->find($queuedLoopMessage->getQueuedReproducePathId());

        if (!$queuedReproducePath || !$queuedReproducePath instanceof QueuedReproducePath) {
            return;
        }

        $model = $this->modelRegistry->getModel($queuedReproducePath->getReproducePath()->getTask()->getModel());
        $graph = $this->graphBuilder->build($model->getDefinition());
        $path  = Path::fromSteps($queuedReproducePath->getReproducePath()->getSteps(), $graph);

        if ($queuedReproducePath->getReproducePath()->getLength() >= $queuedLoopMessage->getLength()) {
            // The reproduce path has not been reduced.
            list($i, $j) = $queuedLoopMessage->getPair();
            if ($j < $path->countVertices() && $path->getVertexAt($i)->getId() === $path->getVertexAt($j)->getId()) {
                $newPath = $this->getNewPath($path, $i, $j);
                // Make sure new path shorter than old path.
                if ($newPath->countEdges() < $path->countEdges()) {
                    try {
                        $this->runner->run($newPath, $model);
                    } catch (Throwable $newThrowable) {
                        if ($newThrowable->getMessage() === $queuedReproducePath->getReproducePath()->getBugMessage()) {
                            $path = $newPath;
                            $updated = $this->updateSteps($queuedReproducePath->getId(), $path, $path->countEdges());
                            if ($updated) {
                                $this->dispatch($queuedReproducePath->getId());
                            }
                        }
                    }
                }
            }
        }

        $this->postHandle($queuedLoopMessage, $path);
    }

    /**
     * @param QueuedLoopMessage $queuedLoopMessage
     * @param Path $path
     * @throws \Exception
     */
    public function postHandle(QueuedLoopMessage $queuedLoopMessage, Path $path)
    {
        $this->entityManager->getConnection()->beginTransaction();
        try {
            $queuedReproducePath = $this->entityManager->find(QueuedReproducePath::class, $queuedLoopMessage->getQueuedReproducePathId(), LockMode::PESSIMISTIC_WRITE);

            if (!$queuedReproducePath || !$queuedReproducePath instanceof QueuedReproducePath) {
                return;
            }

            $messageHashes = $queuedReproducePath->getMessageHashes();
            $hash = sha1($queuedLoopMessage);
            if (($key = array_search($hash, $messageHashes)) !== false) {
                unset($messageHashes[$key]);
            }
            $queuedReproducePath->setMessageHashes($messageHashes);
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
        } catch (Throwable $throwable) {
            // Something happen, ignoring.
            $this->entityManager->getConnection()->rollBack();
        }

        if (isset($queuedReproducePath) && empty($queuedReproducePath->getMessageHashes())) {
            if ($queuedReproducePath->getDistance() > 0) {
                $this->dispatch($queuedReproducePath->getId());
            }
            else {
                // All messages has been handled.
                $this->finish($queuedReproducePath->getId());
            }
        }
    }

    /**
     * @param int $queuedReproducePathId
     * @throws \Exception
     */
    public function dispatch(int $queuedReproducePathId)
    {
        $this->entityManager->getConnection()->beginTransaction();
        try {
            $queuedReproducePath = $this->entityManager->find(QueuedReproducePath::class, $queuedReproducePathId, LockMode::PESSIMISTIC_WRITE);

            if (!$queuedReproducePath || !$queuedReproducePath instanceof QueuedReproducePath) {
                return;
            }

            $model = $this->modelRegistry->getModel($queuedReproducePath->getReproducePath()->getTask()->getModel());
            $graph = $this->graphBuilder->build($model->getDefinition());
            $path  = Path::fromSteps($queuedReproducePath->getReproducePath()->getSteps(), $graph);

            $distance = $queuedReproducePath->getIndicator();
            $pairs = [];
            while ($distance > 0 && empty($pairs)) {
                for ($i = 0; $i < $path->countVertices() - 1; $i++) {
                    $j = $i + $distance;
                    if ($j < $path->countVertices() && $path->getVertexAt($i)->getId() === $path->getVertexAt($j)->getId()) {
                        $pairs[] = [$i, $j];
                    }
                }
                $distance--;
            }

            $messageHashes = [];
            foreach ($pairs as $pair) {
                $message = new QueuedLoopMessage($queuedReproducePath->getId(), $path->countEdges(), $pair);
                $this->messageBus->dispatch($message);
                $messageHashes[] = sha1($message);
            }

            $queuedReproducePath->setIndicator($distance);
            $queuedReproducePath->setMessageHashes(array_unique($messageHashes));
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
        } catch (Throwable $throwable) {
            // Something happen, ignoring.
            $this->entityManager->getConnection()->rollBack();
        }
    }

    /**
     * @param int $reproducePathId
     * @param string $steps
     * @param int $length
     * @return bool
     * @throws \Exception
     */
    private function updateSteps(int $reproducePathId, string $steps, int $length): bool
    {
        $updated = false;
        $this->entityManager->getConnection()->beginTransaction();
        try {
            $reproducePath = $this->entityManager->find(ReproducePath::class, $reproducePathId, LockMode::PESSIMISTIC_WRITE);

            if (!$reproducePath || !$reproducePath instanceof ReproducePath) {
                return $updated;
            }

            $reproducePath->setSteps($steps);
            $reproducePath->setLength($length);
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();

            $updated = true;
        } catch (Throwable $throwable) {
            $this->entityManager->getConnection()->rollBack();
        } finally {
            return $updated;
        }
    }

    public static function getName()
    {
        return 'queued-loop';
    }
}
