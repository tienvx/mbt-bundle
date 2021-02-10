<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;

class RecordVideoMessageHandler implements MessageHandlerInterface
{
    protected BugHelperInterface $bugHelper;

    public function __construct(BugHelperInterface $bugHelper)
    {
        $this->bugHelper = $bugHelper;
    }

    public function __invoke(RecordVideoMessage $message): void
    {
        $this->bugHelper->recordVideo($message->getBugId());
    }
}
