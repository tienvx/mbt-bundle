<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;

class MakeReporterCommandTest extends CommandTestCase
{
    public function testExecute()
    {
        $command = $this->application->find('make:reporter');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'test',
            'reporter-class' => 'Test',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Success!', $output);
        $this->assertStringContainsString('class Test implements ReporterInterface', file_get_contents(__DIR__.'/../app/src/Reporter/Test.php'));
        unlink(__DIR__.'/../app/src/Reporter/Test.php');
    }
}
