<?php

namespace Tienvx\Bundle\MbtBundle\Messenger\Handler;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\ReportBugCommand;
use Tienvx\Bundle\MbtBundle\Messenger\Message\BugMessage;

class BugMessageHandler implements MessageHandlerInterface
{
    private $reportBugCommand;

    public function __construct(ReportBugCommand $reportBugCommand)
    {
        $this->reportBugCommand = $reportBugCommand;
    }

    /**
     * @param BugMessage $bugMessage
     * @throws \Exception
     */
    public function __invoke(BugMessage $bugMessage)
    {
        $input = new ArrayInput([
            'bug-id' => $bugMessage->getId(),
        ]);
        $this->reportBugCommand->run($input, new NullOutput());
    }
}
