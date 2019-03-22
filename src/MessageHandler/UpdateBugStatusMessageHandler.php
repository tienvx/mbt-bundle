<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\UpdateBugStatusMessage;

class UpdateBugStatusMessageHandler implements MessageHandlerInterface
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
     * @param UpdateBugStatusMessage $message
     * @throws Exception
     */
    public function __invoke(UpdateBugStatusMessage $message)
    {
        $bugId = $message->getId();
        $status = $message->getStatus();
        $this->commandRunner->run(['mbt:bug:update-status', $bugId, $status]);
    }
}
