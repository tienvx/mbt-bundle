<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\ApplyTaskTransitionMessage;

class ApplyTaskTransitionMessageHandler implements MessageHandlerInterface
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
     * @param ApplyTaskTransitionMessage $message
     *
     * @throws Exception
     */
    public function __invoke(ApplyTaskTransitionMessage $message)
    {
        $taskId = $message->getId();
        $transition = $message->getTransition();
        $this->commandRunner->run(['mbt:task:apply-transition', $taskId, $transition]);
    }
}
