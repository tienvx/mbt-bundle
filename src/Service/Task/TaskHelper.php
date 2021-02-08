<?php

namespace Tienvx\Bundle\MbtBundle\Service\Task;

use Doctrine\ORM\EntityManagerInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManagerInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManagerInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;

class TaskHelper implements TaskHelperInterface
{
    protected GeneratorManagerInterface $generatorManager;
    protected ProviderManagerInterface $providerManager;
    protected EntityManagerInterface $entityManager;
    protected StepRunnerInterface $stepRunner;
    protected BugHelperInterface $bugHelper;
    protected int $maxSteps;

    public function __construct(
        GeneratorManagerInterface $generatorManager,
        ProviderManagerInterface $providerManager,
        EntityManagerInterface $entityManager,
        StepRunnerInterface $stepRunner,
        BugHelperInterface $bugHelper
    ) {
        $this->generatorManager = $generatorManager;
        $this->providerManager = $providerManager;
        $this->entityManager = $entityManager;
        $this->stepRunner = $stepRunner;
        $this->bugHelper = $bugHelper;
    }

    public function setMaxSteps(int $maxSteps): void
    {
        $this->maxSteps = $maxSteps;
    }

    /**
     * @throws ExceptionInterface
     */
    public function run(int $taskId): void
    {
        $task = $this->getTask($taskId);
        $this->startRunning($task);

        $steps = [];
        $generator = $this->generatorManager->getGenerator($task->getTaskConfig()->getGenerator());
        $driver = $this->providerManager->createDriver($task);
        try {
            foreach ($generator->generate($task) as $step) {
                if ($step instanceof StepInterface) {
                    $task->getProgress()->increase();
                    $this->stepRunner->run($step, $task->getModelRevision(), $driver);
                    $steps[] = clone $step;
                }
                if (count($steps) >= $this->maxSteps) {
                    break;
                }
            }
        } catch (ExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            if (isset($step) && $step instanceof StepInterface) {
                // Last step cause the bug, we can't capture it. We capture it here.
                $steps[] = clone $step;
            }
            $task->addBug($this->bugHelper->createBug($steps, $throwable->getMessage()));
        } finally {
            $driver->quit();
            $this->stopRunning($task, count($steps));
        }
    }

    protected function getTask(int $taskId): TaskInterface
    {
        $task = $this->entityManager->find(Task::class, $taskId);

        if (!$task instanceof TaskInterface) {
            throw new UnexpectedValueException(sprintf('Can not execute task %d: task not found', $taskId));
        }

        return $task;
    }

    protected function startRunning(TaskInterface $task): void
    {
        if ($task->isRunning()) {
            throw new RuntimeException(sprintf('Task %d is already running', $task->getId()));
        } else {
            $task->setRunning(true);
            $task->getProgress()->setProcessed(0);
            $task->getProgress()->setTotal($this->maxSteps);
            $this->entityManager->flush();
        }
    }

    protected function stopRunning(TaskInterface $task, int $stepsCount): void
    {
        $task->setRunning(false);
        $task->getProgress()->setTotal($stepsCount);
        // Running task take long time. Reconnect to flush changes.
        $this->entityManager->getConnection()->connect();
        $this->entityManager->flush();
    }
}
