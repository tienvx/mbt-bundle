<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;

class MakePredefinedCaseCommandTest extends CommandTestCase
{
    public function testExecute()
    {
        $command = $this->application->find('make:predefined-case');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'test',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Success!', $output);
        $this->assertStringContainsString("'test':", file_get_contents(__DIR__.'/../app/config/packages/predefined-cases/test.yaml'));
        unlink(__DIR__.'/../app/config/packages/predefined-cases/test.yaml');
    }
}
