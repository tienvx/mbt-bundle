<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\UpdateTaskStatusMessage;

class UpdateTaskStatusMessageHandler implements MessageHandlerInterface
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
     * @param UpdateTaskStatusMessage $message
     *
     * @throws Exception
     */
    public function __invoke(UpdateTaskStatusMessage $message)
    {
        $taskId = $message->getId();
        $status = $message->getStatus();
        $this->commandRunner->run(['mbt:task:update-status', $taskId, $status]);
    }
}
