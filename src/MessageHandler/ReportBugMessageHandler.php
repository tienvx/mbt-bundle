<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;

class ReportBugMessageHandler implements MessageHandlerInterface
{
    public function __construct(protected BugHelperInterface $bugHelper)
    {
    }

    public function __invoke(ReportBugMessage $message): void
    {
        $this->bugHelper->reportBug($message->getBugId());
    }
}
