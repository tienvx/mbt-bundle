<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\ExecuteCommand;
use Tienvx\Bundle\MbtBundle\Message\TaskMessage;

class TaskMessageHandler implements MessageHandlerInterface
{
    private $executeCommand;

    public function __construct(ExecuteCommand $executeCommand)
    {
        $this->executeCommand = $executeCommand;
    }

    public function __invoke(TaskMessage $taskMessage)
    {
        $arguments = [
            'task-id'      => $taskMessage->getId(),
        ];

        $input = new ArrayInput($arguments);
        $this->testCommand->run($input, new NullOutput());
    }
}
