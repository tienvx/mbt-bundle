<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

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
                            $reproducePath->setSteps($path);
                            $reproducePath->setLength($path->countEdges());
                            $reproducePath->setDistance($path->countEdges());
                            $this->dispatch($reproducePath);
                        }
                    }
                }
            }
        }

        $latestReproducePath = $this->entityManager->getRepository(ReproducePath::class)->find($reproducePath->getId());
        if (!$latestReproducePath || !$latestReproducePath instanceof ReproducePath) {
            return;
        }
        $messageHashes = $latestReproducePath->getMessageHashes();
        $hash = sha1($queuedPathReducerMessage);
        if (($key = array_search($hash, $messageHashes)) !== false) {
            unset($messageHashes[$key]);
        }
        $latestReproducePath->setMessageHashes($messageHashes);
        $this->entityManager->persist($latestReproducePath);
        $this->entityManager->flush();

        if (empty($messageHashes)) {
            if ($latestReproducePath->getDistance() > 0) {
                $this->dispatch($latestReproducePath);
            }
            else {
                // All messages has been handled.
                $this->finish($latestReproducePath->getBugMessage(), $path, $latestReproducePath->getTask()->getId());
            }
        }
    }

    /**
     * @param ReproducePath $reproducePath
     * @throws \Exception
     */
    public function dispatch(ReproducePath $reproducePath)
    {
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

        $latestReproducePath = $this->entityManager->getRepository(ReproducePath::class)->find($reproducePath->getId());
        if (!$latestReproducePath || !$latestReproducePath instanceof ReproducePath) {
            return;
        }
        if ($latestReproducePath->getLength() >= $reproducePath->getLength()) {
            $latestReproducePath->setSteps($reproducePath->getSteps());
            $latestReproducePath->setLength($reproducePath->getLength());
            $latestReproducePath->setDistance($distance);
            $latestReproducePath->setMessageHashes(array_unique($messageHashes));
            $this->entityManager->persist($latestReproducePath);
            $this->entityManager->flush();
        }
    }

    public static function getName()
    {
        return 'queued-loop';
    }
}
