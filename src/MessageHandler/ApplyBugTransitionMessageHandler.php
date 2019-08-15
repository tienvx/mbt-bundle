<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\ApplyBugTransitionMessage;

class ApplyBugTransitionMessageHandler implements MessageHandlerInterface
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
     * @param ApplyBugTransitionMessage $message
     *
     * @throws Exception
     */
    public function __invoke(ApplyBugTransitionMessage $message)
    {
        $bugId = $message->getId();
        $status = $message->getTransition();
        $this->commandRunner->run(['mbt:bug:apply-transition', $bugId, $status]);
    }
}
