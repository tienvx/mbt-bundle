<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Helper\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\QueuedLoopMessage;
use Tienvx\Bundle\MbtBundle\PathReducer\QueuedLoopPathReducer;

class QueuedLoopMessageHandler implements MessageHandlerInterface
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
     * @param QueuedLoopMessage $queuedLoopMessage
     * @throws Exception
     */
    public function __invoke(QueuedLoopMessage $queuedLoopMessage)
    {
        $reducer = QueuedLoopPathReducer::getName();
        $message = $queuedLoopMessage;
        CommandRunner::run($this->kernel, sprintf(sprintf("mbt:handle-path-reducer %s '%s'", $reducer, $message)));
    }
}
