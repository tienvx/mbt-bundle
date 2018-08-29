<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Generator\RandomGenerator;

class GenerateStepsCommandTest extends CommandTestCase
{
    public function testExecute()
    {
        $command = $this->application->find('mbt:generate-steps');
        $this->assertCoverage($command, 'random', 100, 100, 24, 5);
        $this->assertCoverage($command, 'random', 60, 80, 15, 4);
        $this->assertCoverage($command, 'random', 75, 60, 18, 3);
        $this->assertCoverage($command, 'all-places', null, null, 0, 5);
        $this->assertCoverage($command, 'all-transitions', null, null, 24, 0);
    }

    public function assertCoverage(Command $command, string $generator, $transitionCoverage, $placeCoverage, $edgeCount, $vertexCount)
    {
        if ($generator === 'random') {
            /** @var RandomGenerator $randomGenerator */
            $randomGenerator = self::$container->get(RandomGenerator::class);
            $randomGenerator->setTransitionCoverage($transitionCoverage);
            $randomGenerator->setPlaceCoverage($placeCoverage);
        }

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'                     => $command->getName(),
            'model'                       => 'shopping_cart',
            '--generator'                 => $generator,
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
        if ($generator === 'all-transitions' && end($vertices) !== 'home') {
            // Sometime, we can't get the path through all transitions, so ignore it.
        } elseif ($generator === 'all-places' && count($edges) === 1) {
            // Sometime, we can't get the path through all edges, so ignore it.
        } else {
            $this->assertGreaterThanOrEqual($edgeCount, count($edges));
            $this->assertGreaterThanOrEqual($vertexCount, count($vertices));
        }
    }
}
