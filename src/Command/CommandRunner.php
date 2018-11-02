<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

class CommandRunner
{
    /**
     * @var Kernel
     */
    protected $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param string $command
     * @throws \Exception
     */
    public function run(string $command)
    {
        $process = new Process(sprintf('bin/console %s', $command));
        $process->setTimeout(null);
        $process->setWorkingDirectory($this->kernel->getProjectDir());
        $process->disableOutput();

        $process->run();
    }
}
