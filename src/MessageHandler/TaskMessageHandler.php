<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\ExecuteTaskCommand;
use Tienvx\Bundle\MbtBundle\Message\TaskMessage;

class TaskMessageHandler implements MessageHandlerInterface
{
    private $executeTaskCommand;

    public function __construct(ExecuteTaskCommand $executeTaskCommand)
    {
        $this->executeTaskCommand = $executeTaskCommand;
    }

    public function __invoke(TaskMessage $taskMessage)
    {
        $input = new ArrayInput([
            'task-id' => $taskMessage->getId(),
        ]);
        $this->executeTaskCommand->run($input, new NullOutput());
    }
}
