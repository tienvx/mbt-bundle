<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;

class ReduceStepsMessageHandler implements MessageHandlerInterface
{
    protected BugHelperInterface $bugHelper;

    public function __construct(BugHelperInterface $bugHelper)
    {
        $this->bugHelper = $bugHelper;
    }

    public function __invoke(ReduceStepsMessage $message): void
    {
        $this->bugHelper->reduceSteps(
            $message->getBugId(),
            $message->getLength(),
            $message->getFrom(),
            $message->getTo()
        );
    }
}
