<?php

namespace Tienvx\Bundle\MbtBundle\Service\Task;

use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Throwable;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManagerInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Repository\TaskRepositoryInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;

class TaskHelper implements TaskHelperInterface
{
    protected GeneratorManagerInterface $generatorManager;
    protected TaskRepositoryInterface $taskRepository;
    protected StepsRunnerInterface $stepsRunner;
    protected BugHelperInterface $bugHelper;
    protected ConfigInterface $config;

    public function __construct(
        GeneratorManagerInterface $generatorManager,
        TaskRepositoryInterface $taskRepository,
        StepsRunnerInterface $stepsRunner,
        BugHelperInterface $bugHelper,
        ConfigInterface $config
    ) {
        $this->generatorManager = $generatorManager;
        $this->taskRepository = $taskRepository;
        $this->stepsRunner = $stepsRunner;
        $this->bugHelper = $bugHelper;
        $this->config = $config;
    }

    public function run(int $taskId): void
    {
        $task = $this->taskRepository->find($taskId);

        if (!$task instanceof TaskInterface) {
            throw new UnexpectedValueException(sprintf('Can not run task %d: task not found', $taskId));
        }

        if ($task->isRunning()) {
            throw new RecoverableMessageHandlingException(
                sprintf('Can not run task %d: task is running. Will retry later', $task->getId())
            );
        }

        $this->taskRepository->startRunning($task);

        $steps = [];
        $this->stepsRunner->run(
            $this->generatorManager->getGenerator($this->config->getGenerator())->generate($task),
            $task,
            $task->isDebug(),
            function (Throwable $throwable, ?StepInterface $step) use ($task, &$steps): void {
                if ($step instanceof StepInterface) {
                    // Last step cause the bug, we can't capture it. We capture it here.
                    $steps[] = clone $step;
                }
                $task->addBug($this->bugHelper->createBug($steps, $throwable->getMessage()));
            },
            function (StepInterface $step) use (&$steps): bool {
                $steps[] = clone $step;

                return count($steps) >= $this->config->getMaxSteps();
            }
        );

        $this->taskRepository->stopRunning($task);
    }
}
