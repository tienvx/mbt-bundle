<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Message\QueuedPathReducerMessage;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\PathRunner;

class QueuedLoopPathReducer extends AbstractPathReducer implements QueuedPathReducerInterface
{
    protected $messageBus;
    protected $modelRegistry;
    protected $graphBuilder;
    protected $entityManager;

    public function __construct(
        PathRunner $runner,
        EventDispatcherInterface $dispatcher,
        MessageBusInterface $messageBus,
        ModelRegistry $modelRegistry,
        GraphBuilder $graphBuilder,
        EntityManagerInterface $entityManager)
    {
        parent::__construct($runner, $dispatcher);
        $this->messageBus    = $messageBus;
        $this->modelRegistry = $modelRegistry;
        $this->graphBuilder  = $graphBuilder;
        $this->entityManager = $entityManager;
    }

    public function reduce(Path $path, Model $model, string $bugMessage, $taskId = null)
    {
        $reproducePath = new ReproducePath();
        $reproducePath->setModel($model->getName());
        $reproducePath->setSteps($path);
        $reproducePath->setLength($path->countEdges());
        $reproducePath->setMessageHashes([]);
        $reproducePath->setBugMessage($bugMessage);
        $reproducePath->setReducer(static::getName());
        $reproducePath->setDistance($path->countEdges());

        if ($taskId) {
            $task = $this->entityManager->getRepository(Task::class)->find($taskId);

            if ($task instanceof Task) {
                $reproducePath->setTask($task);
            }
        }

        $this->entityManager->persist($reproducePath);
        $this->entityManager->flush();
    }

    /**
     * @param QueuedPathReducerMessage $queuedPathReducerMessage
     * @throws \Exception
     */
    public function handle(QueuedPathReducerMessage $queuedPathReducerMessage)
    {
        $reproducePath = $this->entityManager->getRepository(ReproducePath::class)->find($queuedPathReducerMessage->getReproducePathId());

        if (!$reproducePath || !$reproducePath instanceof ReproducePath) {
            return;
        }

        $model = $this->modelRegistry->getModel($reproducePath->getModel());
        $graph = $this->graphBuilder->build($model->getDefinition());
        $path  = Path::fromSteps($reproducePath->getSteps(), $graph);

        if ($reproducePath->getLength() >= $queuedPathReducerMessage->getLength()) {
            // The reproduce path has not been reduced.
            list($i, $j) = $queuedPathReducerMessage->getPair();
            if ($j < $path->countVertices() && $path->getVertexAt($i)->getId() === $path->getVertexAt($j)->getId()) {
                $newPath = $this->getNewPath($path, $i, $j);
                // Make sure new path shorter than old path.
                if ($newPath->countEdges() < $path->countEdges()) {
                    try {
                        $this->runner->run($newPath, $model);
                    } catch (Throwable $newThrowable) {
                        if ($newThrowable->getMessage() === $reproducePath->getBugMessage()) {
                            $path = $newPath;
                            $updated = $this->updateSteps($reproducePath->getId(), $path, $path->countEdges());
                            if ($updated) {
                                $this->dispatch($reproducePath->getId());
                            }
                        }
                    }
                }
            }
        }

        $this->postHandle($queuedPathReducerMessage, $path);
    }

    /**
     * @param QueuedPathReducerMessage $queuedPathReducerMessage
     * @param Path $path
     * @throws \Exception
     */
    public function postHandle(QueuedPathReducerMessage $queuedPathReducerMessage, Path $path)
    {
        $this->entityManager->getConnection()->beginTransaction();
        try {
            $reproducePath = $this->entityManager->find(ReproducePath::class, $queuedPathReducerMessage->getReproducePathId(), LockMode::PESSIMISTIC_WRITE);

            if (!$reproducePath || !$reproducePath instanceof ReproducePath) {
                return;
            }

            $messageHashes = $reproducePath->getMessageHashes();
            $hash = sha1($queuedPathReducerMessage);
            if (($key = array_search($hash, $messageHashes)) !== false) {
                unset($messageHashes[$key]);
            }
            $reproducePath->setMessageHashes($messageHashes);
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
        } catch (Throwable $throwable) {
            // Another worker has already reduced the reproduce path, try again.
            $this->entityManager->getConnection()->rollBack();
            sleep(1);
            $this->postHandle($queuedPathReducerMessage, $path);
            return;
        }

        if (empty($reproducePath->getMessageHashes())) {
            if ($reproducePath->getDistance() > 0) {
                $this->dispatch($reproducePath->getId());
            }
            else {
                // All messages has been handled.
                $this->finish($reproducePath->getBugMessage(), $path, $reproducePath->getTask()->getId());
            }
        }
    }

    /**
     * @param int $id
     * @throws \Exception
     */
    public function dispatch(int $id)
    {
        $this->entityManager->getConnection()->beginTransaction();
        try {
            $reproducePath = $this->entityManager->find(ReproducePath::class, $id, LockMode::PESSIMISTIC_WRITE);

            if (!$reproducePath || !$reproducePath instanceof ReproducePath) {
                return;
            }

            $model = $this->modelRegistry->getModel($reproducePath->getModel());
            $graph = $this->graphBuilder->build($model->getDefinition());
            $path  = Path::fromSteps($reproducePath->getSteps(), $graph);

            $distance = $reproducePath->getDistance();
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
                $message = new QueuedPathReducerMessage($reproducePath->getId(), $path->countEdges(), $pair, static::getName());
                $this->messageBus->dispatch($message);
                $messageHashes[] = sha1($message);
            }

            $reproducePath->setDistance($distance);
            $reproducePath->setMessageHashes(array_unique($messageHashes));
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
        } catch (Throwable $throwable) {
            // Another worker has already reduced the reproduce path, ignoring.
            $this->entityManager->getConnection()->rollBack();
        }
    }

    /**
     * @param int $id
     * @param string $steps
     * @param int $length
     * @return bool
     * @throws \Exception
     */
    private function updateSteps(int $id, string $steps, int $length): bool
    {
        $updated = false;
        $this->entityManager->getConnection()->beginTransaction();
        try {
            $reproducePath = $this->entityManager->find(ReproducePath::class, $id, LockMode::PESSIMISTIC_WRITE);

            if (!$reproducePath || !$reproducePath instanceof ReproducePath) {
                return $updated;
            }

            $reproducePath->setSteps($steps);
            $reproducePath->setLength($length);
            $reproducePath->setDistance($length);
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
