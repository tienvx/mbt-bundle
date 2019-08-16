<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\FinishReduceBugMessage;

class FinishReduceBugMessageHandler implements MessageHandlerInterface
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
     * @param FinishReduceBugMessage $message
     *
     * @throws Exception
     */
    public function __invoke(FinishReduceBugMessage $message)
    {
        $id = $message->getId();
        $this->commandRunner->run(['mbt:bug:finish-reduce', $id]);
    }
}
