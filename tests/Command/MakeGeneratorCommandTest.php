<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;

class MakeGeneratorCommandTest extends CommandTestCase
{
    public function testExecute()
    {
        $command = $this->application->find('make:generator');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name' => 'test',
            'generator-class' => 'Test',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Success!', $output);
        $this->assertStringContainsString('class Test implements GeneratorInterface', file_get_contents(__DIR__.'/../app/src/Generator/Test.php'));
        unlink(__DIR__.'/../app/src/Generator/Test.php');
    }
}
