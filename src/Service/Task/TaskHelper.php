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
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;

class TaskHelper implements TaskHelperInterface
{
    protected GeneratorManagerInterface $generatorManager;
    protected EntityManagerInterface $entityManager;
    protected StepRunnerInterface $stepRunner;
    protected BugHelperInterface $bugHelper;
    protected SelenoidHelperInterface $selenoidHelper;
    protected ConfigInterface $config;

    public function __construct(
        GeneratorManagerInterface $generatorManager,
        EntityManagerInterface $entityManager,
        StepRunnerInterface $stepRunner,
        BugHelperInterface $bugHelper,
        SelenoidHelperInterface $selenoidHelper,
        ConfigInterface $config
    ) {
        $this->generatorManager = $generatorManager;
        $this->entityManager = $entityManager;
        $this->stepRunner = $stepRunner;
        $this->bugHelper = $bugHelper;
        $this->selenoidHelper = $selenoidHelper;
        $this->config = $config;
    }

    /**
     * @throws ExceptionInterface
     */
    public function run(int $taskId): void
    {
        $task = $this->getTask($taskId);
        $this->startRunning($task);

        $steps = [];
        try {
            $generator = $this->generatorManager->getGenerator($this->config->getGenerator());
            $driver = $this->selenoidHelper->createDriver($this->selenoidHelper->getCapabilities($task));
            foreach ($generator->generate($task) as $step) {
                if ($step instanceof StepInterface) {
                    $this->stepRunner->run($step, $task->getModelRevision(), $driver);
                    $steps[] = clone $step;
                }
                if (count($steps) >= $this->config->getMaxSteps()) {
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
            if (isset($driver)) {
                $driver->quit();
            }
            $this->stopRunning($task);
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
            $this->entityManager->flush();
        }
    }

    protected function stopRunning(TaskInterface $task): void
    {
        $task->setRunning(false);
        // Running task take long time. Reconnect to flush changes.
        $this->entityManager->getConnection()->connect();
        $this->entityManager->flush();
    }
}
