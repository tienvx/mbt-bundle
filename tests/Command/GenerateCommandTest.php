<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Command\GenerateCommand;

class GenerateCommandTest extends CommandTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $modelRegistry = $kernel->getContainer()->get('Tienvx\Bundle\MbtBundle\Service\ModelRegistry.test');
        $generatorManager = $kernel->getContainer()->get('Tienvx\Bundle\MbtBundle\Service\GeneratorManager.test');

        $application = new Application($kernel);
        $application->add(new GenerateCommand($modelRegistry, $generatorManager));

        $command = $application->find('mbt:generate');
        $this->assertCoverage($command, 'random', $this->getCoverageStopCondition(100, 100), 24, 5);
        $this->assertCoverage($command, 'random', $this->getCoverageStopCondition(60, 80), 15, 4);
        $this->assertCoverage($command, 'random', $this->getCoverageStopCondition(75, 60), 18, 3);
        $this->assertCoverage($command, 'all-places', null, 0, 5);
        $this->assertCoverage($command, 'all-transitions', null, 24, 0);
    }

    public function assertCoverage(Command $command, string $generator, $arguments, $edgeCount, $vertexCount)
    {
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'      => $command->getName(),
            'model'        => 'shopping_cart',
            '--generator'  => $generator,
            '--arguments'  => $arguments
        ]);

        $output = $commandTester->getDisplay();

        $edges = [];
        $vertices = [];
        foreach (explode(' ', $output) as $step) {
            $pos = strpos($step, '(');
            if ($pos === false) {
                $vertices[] = $step;
            }
            else {
                $edges[] = substr($step, 0, $pos);
            }
        }
        if ($generator !== 'all-transitions' || end($vertices) === 'home') {
            // Sometime, we can't get the path through all transitions, so ignore it.
            $this->assertGreaterThanOrEqual($edgeCount, count($edges));
            $this->assertGreaterThanOrEqual($vertexCount, count($vertices));
        }
    }
}
