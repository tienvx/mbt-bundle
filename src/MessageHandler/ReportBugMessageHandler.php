<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;

class ReportBugMessageHandler implements MessageHandlerInterface
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
     * @param ReportBugMessage $message
     * @throws Exception
     */
    public function __invoke(ReportBugMessage $message)
    {
        $bugId = $message->getBugId();
        $this->commandRunner->run(['mbt:bug:report', $bugId]);
    }
}
