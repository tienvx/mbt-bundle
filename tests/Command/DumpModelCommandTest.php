<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Command\DumpModelCommand;
use Tienvx\Bundle\MbtBundle\Model\ModelRegistry;

class DumpModelCommandTest extends CommandTestCase
{
    public function testExecute()
    {
        /** @var ModelRegistry $modelRegistry */
        $modelRegistry = self::$container->get(ModelRegistry::class);
        /** @var Registry $workflows */
        $workflows = self::$container->get(Registry::class);

        $this->application->add(new DumpModelCommand($modelRegistry, $workflows));

        $command = $this->application->find('mbt:dump-model');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'    => $command->getName(),
            'model'      => 'shopping_cart',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('digraph workflow {', $output);
    }
}
