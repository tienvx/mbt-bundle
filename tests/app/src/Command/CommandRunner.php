<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner as BaseCommandRunner;

class CommandRunner extends BaseCommandRunner
{
    /**
     * @param string $command
     * @throws \Exception
     */
    public function run(string $command)
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $application->run(new StringInput($command), new NullOutput());
    }
}
