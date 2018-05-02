<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\ReportBugCommand;
use Tienvx\Bundle\MbtBundle\Message\BugMessage;

class BugMessageHandler implements MessageHandlerInterface
{
    private $reportBugCommand;

    public function __construct(ReportBugCommand $reportBugCommand)
    {
        $this->reportBugCommand = $reportBugCommand;
    }

    public function __invoke(BugMessage $bugMessage)
    {
        $input = new ArrayInput([
            'bug-id' => $bugMessage->getId(),
        ]);
        $this->reportBugCommand->run($input, new NullOutput());
    }
}
