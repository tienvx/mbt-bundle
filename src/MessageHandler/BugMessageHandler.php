<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\BugMessage;

class BugMessageHandler implements MessageHandlerInterface
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
     * @param BugMessage $bugMessage
     * @throws Exception
     */
    public function __invoke(BugMessage $bugMessage)
    {
        $id = $bugMessage->getId();
        $this->commandRunner->run(['mbt:bug:reduce', $id]);
    }
}
