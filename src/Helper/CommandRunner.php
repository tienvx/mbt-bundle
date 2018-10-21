<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Process\Process;

class CommandRunner
{
    /**
     * @param Kernel $kernel
     * @param string $command
     * @throws \Exception
     */
    public static function run(Kernel $kernel, string $command)
    {
        if ($kernel->getEnvironment() === 'test') {
            $application = new Application($kernel);
            $application->setAutoExit(false);
            $application->run(new StringInput($command), new NullOutput());
        } else {
            $process = new Process(sprintf('bin/console %s', $command));
            $process->setTimeout(null);
            $process->setWorkingDirectory($kernel->getProjectDir());
            $process->disableOutput();

            $process->run();
        }
    }
}
