<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\FinishReducePathMessage;

class FinishReducePathMessageHandler implements MessageHandlerInterface
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
     * @param FinishReducePathMessage $message
     *
     * @throws Exception
     */
    public function __invoke(FinishReducePathMessage $message)
    {
        $bugId = $message->getBugId();
        $this->commandRunner->run(['mbt:path:finish-reduce', $bugId]);
    }
}
