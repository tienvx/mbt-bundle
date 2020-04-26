<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Exception;
use Symfony\Component\Console\Tester\CommandTester;

class WorkflowTryCommandTest extends CommandTestCase
{
    /**
     * @throws Exception
     */
    public function testExecute()
    {
        $name = 'mbt:workflow:try';
        $input = [
            'command' => $name,
            'workflow-name' => 'checkout',
            '--generator' => 'random',
            '--max-steps' => 300,
            '--transition-coverage' => 100,
            '--place-coverage' => 100,
        ];

        $command = $this->application->find($name);
        $commandTester = new CommandTester($command);
        $commandTester->execute($input);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Trying workflow is finished!', $output);
    }
}
