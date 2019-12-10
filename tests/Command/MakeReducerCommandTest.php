<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;

class MakeReducerCommandTest extends CommandTestCase
{
    public function testExecute()
    {
        $command = $this->application->find('make:reducer');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'test',
            'reducer-class' => 'Test',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Success!', $output);
        $this->assertStringContainsString('class Test extends ReducerTemplate', file_get_contents(__DIR__.'/../app/src/Reducer/Test.php'));
        unlink(__DIR__.'/../app/src/Reducer/Test.php');
    }
}
