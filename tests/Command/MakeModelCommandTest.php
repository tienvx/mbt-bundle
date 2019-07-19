<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;

class MakeModelCommandTest extends CommandTestCase
{
    public function testExecute()
    {
        $command = $this->application->find('make:model');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'test',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('Success!', $output);
        $this->assertContains("'test':", file_get_contents(__DIR__.'/../app/config/packages/models/test.yaml'));
        unlink(__DIR__.'/../app/config/packages/models/test.yaml');
    }
}
