<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

class CommandRunner
{
    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->kernel = $kernel;
        $this->logger = $logger;
    }

    /**
     * Call console command corresponding to the message is being handled.
     *
     * @param array $parameters
     *
     * @throws Exception
     */
    public function run(array $parameters)
    {
        $process = new Process(array_merge(['bin/console'], $parameters));
        $process->setTimeout(null);
        $process->setWorkingDirectory($this->kernel->getProjectDir());

        $exitCode = $process->run();

        if ($exitCode) {
            $error = $process->getErrorOutput();
            $this->logger->error($error);

            // Re-sent the message to the transport to be tried again
            throw new Exception($error);
        }
    }
}
