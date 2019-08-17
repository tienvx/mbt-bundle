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
        $this->assertContains('Success!', $output);
        $this->assertContains('class Test extends AbstractReducer', file_get_contents(__DIR__.'/../app/src/Reducer/Test.php'));
        unlink(__DIR__.'/../app/src/Reducer/Test.php');
    }
}
