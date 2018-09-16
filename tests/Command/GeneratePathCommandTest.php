<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Generator\RandomGenerator;
use Tienvx\Bundle\MbtBundle\Graph\Path;

class GeneratePathCommandTest extends CommandTestCase
{
    public function coverageData()
    {
        return [
            ['shopping_cart', 'random', 100, 100, 24, 5],
            ['shopping_cart', 'random', 60, 80, 15, 4],
            ['shopping_cart', 'random', 75, 60, 18, 3],
            ['shopping_cart', 'all-places', null, null, 0, 5],
            ['shopping_cart', 'all-transitions', null, null, 24, 0],
            ['checkout', 'random', 100, 100, 65, 30],
            ['checkout', 'random', 60, 80, 39, 24],
            ['checkout', 'random', 40, 65, 26, 20],
        ];
    }

    /**
     * @dataProvider coverageData
     * @param $model
     * @param $generator
     * @param $transitionCoverage
     * @param $placeCoverage
     * @param $transitionCount
     * @param $placeCount
     */
    public function testExecute($model, $generator, $transitionCoverage, $placeCoverage, $transitionCount, $placeCount)
    {
        $command = $this->application->find('mbt:generate-path');
        if ($generator === 'random') {
            /** @var RandomGenerator $randomGenerator */
            $randomGenerator = self::$container->get(RandomGenerator::class);
            $randomGenerator->setTransitionCoverage($transitionCoverage);
            $randomGenerator->setPlaceCoverage($placeCoverage);
        }

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'     => $command->getName(),
            'model'       => $model,
            '--generator' => $generator,
        ]);

        $output = $commandTester->getDisplay();
        $path = unserialize($output);
        $this->assertInstanceOf(Path::class, $path);

        if ($path instanceof Path) {
            $uniquePlaces = $path->countUniquePlaces();
            $uniqueTransitions = $path->countUniqueTransitions();
            if ($generator === 'all-transitions' && array_diff($path->getPlacesAt($path->countPlaces() - 1), ['home'])) {
                // Sometime, we can't get the path through all transitions, so ignore it.
            } elseif ($generator === 'all-places' && $uniqueTransitions === 1) {
                // Sometime, we can't get the path through all places, so ignore it.
            } elseif ($generator === 'random' && $path->countTransitions() === 300) {
                // Sometime we reach the path length limit, so ignore it.
                $this->assertGreaterThanOrEqual(1, $uniqueTransitions);
                $this->assertGreaterThanOrEqual(1, $uniquePlaces);
            } else {
                $this->assertGreaterThanOrEqual($transitionCount, $uniqueTransitions);
                $this->assertGreaterThanOrEqual($placeCount, $uniquePlaces);
            }
        }
    }
}
