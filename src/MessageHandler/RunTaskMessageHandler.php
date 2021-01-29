<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Message\RunTaskMessage;
use Tienvx\Bundle\MbtBundle\Service\Task\TaskHelperInterface;

class RunTaskMessageHandler implements MessageHandlerInterface
{
    protected TaskHelperInterface $taskHelper;

    public function __construct(TaskHelperInterface $taskHelper)
    {
        $this->taskHelper = $taskHelper;
    }

    public function __invoke(RunTaskMessage $message): void
    {
        $this->taskHelper->run($message->getId());
    }
}
