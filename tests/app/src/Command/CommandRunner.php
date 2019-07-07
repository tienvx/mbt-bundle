<?php

namespace App\Command;

use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner as BaseCommandRunner;

class CommandRunner extends BaseCommandRunner
{
    /**
     * @param array $parameters
     *
     * @throws Exception
     */
    public function run(array $parameters)
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $map = [
            'mbt:bug:capture-screenshots' => ['command', 'bug-id'],
            'mbt:bug:create' => ['command', 'title', 'path', 'length', 'message', 'task-id', 'status'],
            'mbt:task:execute' => ['command', 'task-id'],
            'mbt:bug:reduce' => ['command', 'bug-id', 'reducer'],
            'mbt:path:reduce' => ['command', 'bug-id', 'reducer', 'length', 'from', 'to'],
            'mbt:bug:report' => ['command', 'bug-id', 'reporter'],
            'mbt:task:update-status' => ['command', 'task-id', 'status'],
            'mbt:bug:update-status' => ['command', 'bug-id', 'status'],
            'mbt:bug:remove-screenshots' => ['command', 'bug-id', 'model'],
        ];
        $command = $parameters[0];
        $application->run(new ArrayInput(array_combine($map[$command], $parameters)));
    }
}
