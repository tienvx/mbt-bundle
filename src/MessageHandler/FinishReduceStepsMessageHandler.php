<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\FinishReduceStepsMessage;

class FinishReduceStepsMessageHandler implements MessageHandlerInterface
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
     * @param FinishReduceStepsMessage $message
     *
     * @throws Exception
     */
    public function __invoke(FinishReduceStepsMessage $message)
    {
        $bugId = $message->getBugId();
        $this->commandRunner->run(['mbt:steps:finish-reduce', $bugId]);
    }
}
