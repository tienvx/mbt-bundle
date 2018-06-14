<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
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
    private   $modelRegistry;
    private   $graphBuilder;
    private   $entityManager;

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
                            $this->dispatchNewReproducePath($reproducePath->getId(), $path, $path->countEdges());
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
        $reproducePath = $this->entityManager->getRepository(ReproducePath::class)->find($queuedPathReducerMessage->getReproducePathId());

        if (!$reproducePath || !$reproducePath instanceof ReproducePath) {
            return;
        }

        try {
            $this->entityManager->lock($reproducePath, LockMode::OPTIMISTIC, $reproducePath->getVersion());

            $messageHashes = $reproducePath->getMessageHashes();
            $hash = sha1($queuedPathReducerMessage);
            if (($key = array_search($hash, $messageHashes)) !== false) {
                unset($messageHashes[$key]);
            }
            $reproducePath->setMessageHashes($messageHashes);
            $this->entityManager->persist($reproducePath);
            $this->entityManager->flush();

            if (empty($reproducePath->getMessageHashes())) {
                if ($reproducePath->getDistance() > 0) {
                    $this->dispatch($reproducePath);
                }
                else {
                    // All messages has been handled.
                    $this->finish($reproducePath->getBugMessage(), $path, $reproducePath->getTask()->getId());
                }
            }
        } catch (OptimisticLockException $e) {
            // Another worker has already reduced the reproduce path, try again.
            $this->postHandle($queuedPathReducerMessage, $path);
        }
    }

    /**
     * @param ReproducePath $reproducePath
     * @throws \Exception
     */
    public function dispatch(ReproducePath $reproducePath)
    {
        try {
            $this->entityManager->lock($reproducePath, LockMode::OPTIMISTIC, $reproducePath->getVersion());

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
            $this->entityManager->persist($reproducePath);
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            // Another worker has already reduced the reproduce path, ignoring.
        }
    }

    /**
     * @param int $id
     * @param string $steps
     * @param int $length
     * @throws \Exception
     */
    private function dispatchNewReproducePath(int $id, string $steps, int $length)
    {
        $reproducePath = $this->entityManager->find(ReproducePath::class, $id);

        if (!$reproducePath || !$reproducePath instanceof ReproducePath) {
            return;
        }

        $reproducePath->setSteps($steps);
        $reproducePath->setLength($length);
        $reproducePath->setDistance($length);
        $this->dispatch($reproducePath);
    }

    public static function getName()
    {
        return 'queued-loop';
    }
}
