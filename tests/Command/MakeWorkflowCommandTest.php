<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;

class MakeWorkflowCommandTest extends CommandTestCase
{
    public function testExecute()
    {
        $command = $this->application->find('make:workflow');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'test',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Success!', $output);
        $this->assertStringContainsString("'test':", file_get_contents(__DIR__.'/../app/config/packages/workflows/test.yaml'));
        unlink(__DIR__.'/../app/config/packages/workflows/test.yaml');
    }
}
