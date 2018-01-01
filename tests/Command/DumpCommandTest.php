<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Command\DumpCommand;

class DumpCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new DumpCommand($kernel->getContainer()->get('Tienvx\Bundle\MbtBundle\Service\ModelRegistry.test')));

        $command = $application->find('mbt:dump');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'      => $command->getName(),
            'model'      => 'shopping_cart',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('digraph workflow {', $output);
    }
}
