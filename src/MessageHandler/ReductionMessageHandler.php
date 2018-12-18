<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\ReductionMessage;

class ReductionMessageHandler implements MessageHandlerInterface
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
     * @param ReductionMessage $message
     * @throws Exception
     */
    public function __invoke(ReductionMessage $message)
    {
        $bugId = $message->getBugId();
        $reducer = $message->getReducer();
        $data = $message->getData();
        $this->commandRunner->run(['mbt:path:reduce', $bugId, $reducer, "'" . json_encode($data) . "'"]);
    }
}
