<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Helper\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\TaskMessage;

class TaskMessageHandler implements MessageHandlerInterface
{
    /**
     * @var Kernel
     */
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param TaskMessage $taskMessage
     * @throws Exception
     */
    public function __invoke(TaskMessage $taskMessage)
    {
        $id = $taskMessage->getId();
        CommandRunner::run($this->kernel, [
            'command' => 'mbt:execute-task',
            'task-id'  => $id,
        ], sprintf('bin/console mbt:execute-task %d', $id));
    }
}
