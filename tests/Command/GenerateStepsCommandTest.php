<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Generator\RandomGenerator;
use Tienvx\Bundle\MbtBundle\Graph\Path;

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

    public function assertCoverage(Command $command, string $generator, $transitionCoverage, $placeCoverage, $transitionCount, $placeCount)
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
        $path = unserialize($output);
        $this->assertInstanceOf(Path::class, $path);

        if ($path instanceof Path) {
            $placeInPathCount = count(array_unique(call_user_func_array('array_merge', $path->getAllPlaces())));
            $transitionInPathCount = count(array_unique($path->getAllTransitions()));
            $allPlaces = $path->getAllPlaces();
            if ($generator === 'all-transitions' && array_diff(end($allPlaces), ['home'])) {
                // Sometime, we can't get the path through all transitions, so ignore it.
            } elseif ($generator === 'all-places' && $transitionInPathCount === 1) {
                // Sometime, we can't get the path through all places, so ignore it.
            } else {
                $this->assertGreaterThanOrEqual($transitionCount, $transitionInPathCount);
                $this->assertGreaterThanOrEqual($placeCount, $placeInPathCount);
            }
        }
    }
}
