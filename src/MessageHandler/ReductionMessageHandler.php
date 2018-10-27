<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Helper\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\ReductionMessage;

class ReductionMessageHandler implements MessageHandlerInterface
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
     * @param ReductionMessage $message
     * @throws Exception
     */
    public function __invoke(ReductionMessage $message)
    {
        $bugId = $message->getBugId();
        $reducer = $message->getReducer();
        $data = $message->getData();
        CommandRunner::run($this->kernel, sprintf("mbt:reduce-path %d %s '%s'", $bugId, $reducer, json_encode($data)));
    }
}
