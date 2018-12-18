<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner as BaseCommandRunner;

class CommandRunner extends BaseCommandRunner
{
    /**
     * @param array $parameters
     * @throws \Exception
     */
    public function run(array $parameters)
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $application->run(new StringInput(implode(' ', $parameters)), new NullOutput());
    }
}
