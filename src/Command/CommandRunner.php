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
     * @param array $parameters
     * @throws \Exception
     */
    public function run(array $parameters)
    {
        $process = new Process(array_merge(['bin/console'], $parameters));
        $process->setTimeout(null);
        $process->setWorkingDirectory($this->kernel->getProjectDir());
        $process->disableOutput();

        $process->run();
    }
}
