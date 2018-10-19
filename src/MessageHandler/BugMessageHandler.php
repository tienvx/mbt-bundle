<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Helper\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\BugMessage;

class BugMessageHandler implements MessageHandlerInterface
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
     * @param BugMessage $bugMessage
     * @throws Exception
     */
    public function __invoke(BugMessage $bugMessage)
    {
        $id = $bugMessage->getId();
        CommandRunner::run($this->kernel, [
            'command' => 'mbt:reduce-steps',
            'bug-id'  => $id,
        ], sprintf('bin/console mbt:reduce-steps %d', $id));
    }
}
