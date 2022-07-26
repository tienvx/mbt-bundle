<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Message\RunTaskMessage;
use Tienvx\Bundle\MbtBundle\Service\Task\TaskHelperInterface;

class RunTaskMessageHandler implements MessageHandlerInterface
{
    public function __construct(protected TaskHelperInterface $taskHelper)
    {
    }

    public function __invoke(RunTaskMessage $message): void
    {
        $this->taskHelper->run($message->getId());
    }
}
