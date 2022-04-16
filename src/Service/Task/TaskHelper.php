<?php

namespace Tienvx\Bundle\MbtBundle\Service\Task;

use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManagerInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Repository\TaskRepositoryInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\TaskStepsRunner;

class TaskHelper implements TaskHelperInterface
{
    protected GeneratorManagerInterface $generatorManager;
    protected TaskRepositoryInterface $taskRepository;
    protected TaskStepsRunner $stepsRunner;
    protected ConfigInterface $config;

    public function __construct(
        GeneratorManagerInterface $generatorManager,
        TaskRepositoryInterface $taskRepository,
        TaskStepsRunner $stepsRunner,
        ConfigInterface $config
    ) {
        $this->generatorManager = $generatorManager;
        $this->taskRepository = $taskRepository;
        $this->stepsRunner = $stepsRunner;
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

        $this->stepsRunner->run(
            $this->generatorManager->getGenerator($this->config->getGenerator())->generate($task),
            $task,
            function (BugInterface $bug) use ($task) {
                $task->addBug($bug);
            }
        );

        $this->taskRepository->stopRunning($task);
    }
}
