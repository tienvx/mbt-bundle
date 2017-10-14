<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tienvx\Bundle\MbtBundle\Command\GenerateCommand;

class GenerateCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new GenerateCommand());

        $command = $application->find('mbt:generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'      => $command->getName(),
            '--model'      => 'shopping_cart',
            '--traversal'  => 'random(50,50)'
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('TODO Update this', $output);
    }
}
