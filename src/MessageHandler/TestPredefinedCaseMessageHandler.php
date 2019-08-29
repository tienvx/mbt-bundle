<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\TestPredefinedCaseMessage;

class TestPredefinedCaseMessageHandler implements MessageHandlerInterface
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
     * @param TestPredefinedCaseMessage $message
     *
     * @throws Exception
     */
    public function __invoke(TestPredefinedCaseMessage $message)
    {
        $predefinedCase = $message->getPredefinedCase();
        $this->commandRunner->run(['mbt:predefined-case:test', $predefinedCase]);
    }
}
