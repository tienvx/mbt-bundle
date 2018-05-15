<?php

namespace Tienvx\Bundle\MbtBundle\Messenger\Handler;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\ExecuteTaskCommand;
use Tienvx\Bundle\MbtBundle\Messenger\Message\TaskMessage;

class TaskMessageHandler implements MessageHandlerInterface
{
    private $executeTaskCommand;

    public function __construct(ExecuteTaskCommand $executeTaskCommand)
    {
        $this->executeTaskCommand = $executeTaskCommand;
    }

    /**
     * @param TaskMessage $taskMessage
     * @throws \Exception
     */
    public function __invoke(TaskMessage $taskMessage)
    {
        $input = new ArrayInput([
            'task-id' => $taskMessage->getId(),
        ]);
        $this->executeTaskCommand->run($input, new NullOutput());
    }
}
