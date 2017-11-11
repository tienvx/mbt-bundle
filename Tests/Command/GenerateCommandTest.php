<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
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
        $this->assertCoverage($command, 100, 24, 100, 5);
        $this->assertCoverage($command, 60, 15, 80, 4);
        $this->assertCoverage($command, 75, 18, 60, 3);
    }

    public function assertCoverage(Command $command, $edgeCoverage, $edgeCount, $vertexCoverage, $vertexCount)
    {
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'      => $command->getName(),
            'model'      => 'shopping_cart',
            '--traversal'  => "random({$edgeCoverage},$vertexCoverage)"
        ]);

        $output = $commandTester->getDisplay();
        echo $output . "\n";

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
        $this->assertGreaterThanOrEqual($edgeCount, count($edges));
        $this->assertGreaterThanOrEqual($vertexCount, count($vertices));
    }
}
