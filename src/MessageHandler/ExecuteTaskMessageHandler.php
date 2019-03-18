<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\ExecuteTaskMessage;

class ExecuteTaskMessageHandler implements MessageHandlerInterface
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
     * @param ExecuteTaskMessage $message
     * @throws Exception
     */
    public function __invoke(ExecuteTaskMessage $message)
    {
        $id = $message->getId();
        $this->commandRunner->run(['mbt:task:execute', $id]);
    }
}
