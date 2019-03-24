<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\ReducePathMessage;

class ReducePathMessageHandler implements MessageHandlerInterface
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
     * @param ReducePathMessage $message
     *
     * @throws Exception
     */
    public function __invoke(ReducePathMessage $message)
    {
        $bugId = $message->getBugId();
        $reducer = $message->getReducer();
        $length = $message->getLength();
        $from = $message->getFrom();
        $to = $message->getTo();
        $this->commandRunner->run(['mbt:path:reduce', $bugId, $reducer, $length, $from, $to]);
    }
}
