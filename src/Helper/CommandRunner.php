<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Process\Process;

class CommandRunner
{
    /**
     * @param Kernel $kernel
     * @param array $command
     * @param string $process
     * @throws \Exception
     */
    public static function run(Kernel $kernel, array $command, string $process)
    {
        if ($kernel->getEnvironment() === 'test') {
            $application = new Application($kernel);
            $application->setAutoExit(false);
            $input = new ArrayInput($command);
            $output = new NullOutput();
            $application->run($input, $output);
        } else {
            $process = new Process($process);
            $process->setTimeout(null);
            $process->setWorkingDirectory($kernel->getProjectDir());
            $process->disableOutput();

            $process->run();
        }
    }
}
