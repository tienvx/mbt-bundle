<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\CreateBugMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Repository\TaskRepositoryInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;

class CreateBugMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        protected ConfigInterface $config,
        protected TaskRepositoryInterface $taskRepository
    ) {
    }

    public function __invoke(CreateBugMessage $message): void
    {
        $task = $this->taskRepository->find($message->getTaskId());

        if (!$task instanceof TaskInterface) {
            throw new UnexpectedValueException(sprintf(
                'Can not create bug for task %d: task not found',
                $message->getTaskId()
            ));
        }

        $this->taskRepository->addBug($task, $this->createBug($message->getSteps(), $message->getMessage()));
    }

    public function createBug(array $steps, string $message): BugInterface
    {
        $bug = new Bug();
        $bug->setTitle($this->config->getDefaultBugTitle());
        $bug->setSteps($steps);
        $bug->setMessage($message);

        return $bug;
    }
}
