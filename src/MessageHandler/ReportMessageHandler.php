<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\ReportMessage;

class ReportMessageHandler implements MessageHandlerInterface
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
     * @param ReportMessage $message
     * @throws Exception
     */
    public function __invoke(ReportMessage $message)
    {
        $bugId = $message->getBugId();
        $this->commandRunner->run(['mbt:bug:report', $bugId]);
    }
}
