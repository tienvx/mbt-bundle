<?php

namespace Tienvx\Bundle\MbtBundle\Service\Task;

use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManagerInterface;
use Tienvx\Bundle\MbtBundle\Message\CreateBugMessage;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Repository\TaskRepositoryInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\ExploreStepsRunner;

class TaskHelper implements TaskHelperInterface
{
    public function __construct(
        protected GeneratorManagerInterface $generatorManager,
        protected TaskRepositoryInterface $taskRepository,
        protected MessageBusInterface $messageBus,
        protected ExploreStepsRunner $stepsRunner,
        protected ConfigInterface $config
    ) {
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

        try {
            $this->taskRepository->startRunning($task);

            $this->stepsRunner->run(
                $this->generatorManager->getGenerator($this->config->getGenerator())->generate($task),
                $task,
                function (Throwable $throwable, array $steps) use ($taskId) {
                    $this->messageBus->dispatch(new CreateBugMessage(
                        $taskId,
                        $steps,
                        $throwable->getMessage()
                    ));
                }
            );
        } finally {
            $this->taskRepository->stopRunning($task);
        }
    }
}
