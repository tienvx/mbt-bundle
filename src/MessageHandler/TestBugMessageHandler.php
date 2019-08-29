<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\TestBugMessage;

class TestBugMessageHandler implements MessageHandlerInterface
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
     * @param TestBugMessage $message
     *
     * @throws Exception
     */
    public function __invoke(TestBugMessage $message)
    {
        $bugId = $message->getBugId();
        $this->commandRunner->run(['mbt:bug:test', $bugId]);
    }
}
