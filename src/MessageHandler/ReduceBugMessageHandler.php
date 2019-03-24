<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;

class ReduceBugMessageHandler implements MessageHandlerInterface
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
     * @param ReduceBugMessage $message
     *
     * @throws Exception
     */
    public function __invoke(ReduceBugMessage $message)
    {
        $id = $message->getId();
        $this->commandRunner->run(['mbt:bug:reduce', $id]);
    }
}
