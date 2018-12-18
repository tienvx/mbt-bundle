<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\TaskMessage;

class TaskMessageHandler implements MessageHandlerInterface
{
    /**
     * @var CommandRunner
     */
    private $commandRunner;

    public function __construct(CommandRunner $commandRunner)
    {
        $this->commandRunner = $commandRunner;
    }

    /**
     * @param TaskMessage $taskMessage
     * @throws Exception
     */
    public function __invoke(TaskMessage $taskMessage)
    {
        $id = $taskMessage->getId();
        $this->commandRunner->run(['mbt:task:execute', $id]);
    }
}
