<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\ExecuteTaskCommand;
use Tienvx\Bundle\MbtBundle\Message\BugMessage;

class BugMessageHandler implements MessageHandlerInterface
{
    private $executeCommand;

    public function __construct(ExecuteTaskCommand $executeCommand)
    {
        $this->executeCommand = $executeCommand;
    }

    public function __invoke(BugMessage $bugMessage)
    {
        $input = new ArrayInput([
            'bug-id' => $bugMessage->getId(),
        ]);
        $this->executeCommand->run($input, new NullOutput());
    }
}
