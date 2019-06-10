<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\CreateBugMessage;

class CreateBugMessageHandler implements MessageHandlerInterface
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
     * @param CreateBugMessage $message
     *
     * @throws Exception
     */
    public function __invoke(CreateBugMessage $message)
    {
        $title = $message->getTitle();
        $path = $message->getPath();
        $length = $message->getLength();
        $bugMessage = $message->getMessage();
        $taskId = $message->getTaskId();
        $status = $message->getStatus();
        $this->commandRunner->run(['mbt:bug:create', $title, $path, $length, $bugMessage, $taskId, $status]);
    }
}
