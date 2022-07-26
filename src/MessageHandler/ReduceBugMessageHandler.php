<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;

class ReduceBugMessageHandler implements MessageHandlerInterface
{
    public function __construct(protected BugHelperInterface $bugHelper)
    {
    }

    public function __invoke(ReduceBugMessage $message): void
    {
        $this->bugHelper->reduceBug($message->getId());
    }
}
