<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Command\DumpCommand;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;

class DumpCommandTest extends CommandTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $modelRegistry = self::$container->get(ModelRegistry::class);
        $workflows = self::$container->get(Registry::class);

        $application = new Application($kernel);
        $application->add(new DumpCommand($modelRegistry, $workflows));

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
