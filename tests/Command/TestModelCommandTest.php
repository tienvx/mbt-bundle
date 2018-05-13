<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tienvx\Bundle\MbtBundle\Command\TestModelCommand;
use Tienvx\Bundle\MbtBundle\Service\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\PathReducerManager;
use Tienvx\Bundle\MbtBundle\Service\StopConditionManager;

class TestModelCommandTest extends CommandTestCase
{
    public function testExecute()
    {
        $kernel = static::bootKernel();

        /** @var ModelRegistry $modelRegistry */
        $modelRegistry = self::$container->get(ModelRegistry::class);
        /** @var GeneratorManager $generatorManager */
        $generatorManager = self::$container->get(GeneratorManager::class);
        /** @var PathReducerManager $pathReducerManager */
        $pathReducerManager = self::$container->get(PathReducerManager::class);
        /** @var StopConditionManager $stopConditionManager */
        $stopConditionManager = self::$container->get(StopConditionManager::class);
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = self::$container->get(EventDispatcherInterface::class);

        $application = new Application($kernel);
        $application->add(new TestModelCommand($modelRegistry, $generatorManager, $pathReducerManager, $stopConditionManager, $dispatcher));

        $command = $application->find('mbt:test-model');
        $this->assertReproducePath($command, 'random', 'coverage', '{"edgeCoverage":100,"vertexCoverage":100}');
        $this->assertReproducePath($command, 'random', 'found-bug', '{}');
        $this->assertReproducePath($command, 'all-places', 'null', '{}');
        $this->assertReproducePath($command, 'all-transitions', 'null', '{}');
    }

    public function assertReproducePath(Command $command, string $generator, $stopCondition, $stopConditionArguments)
    {
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'                     => $command->getName(),
            'model'                       => 'shopping_cart',
            '--generator'                 => $generator,
            '--stop-condition'            => $stopCondition,
            '--stop-condition-arguments'  => $stopConditionArguments
        ]);

        $output = $commandTester->getDisplay();
        if (!empty($output)) {
            $this->assertContains('Found a bug: You added an out-of-stock product into cart! Can not checkout', $output);

            // Assert steps.
            $steps = [];
            $lines = explode("\n", $output);
            foreach ($lines as $line) {
                if (strpos($line, '|') === 0 && strpos($line, 'Step') === false) {
                    // Table rows.
                    $cells = explode('|', $line);
                    $cells = array_map('trim', array_values(array_filter($cells)));
                    $step = [];
                    foreach ($cells as $cell) {
                        $step[] = $cell;
                    }
                    $steps[] = $step;
                }
            }
            $productsOutOfStock = [28, 40, 41, 33];
            if (count($steps) === 2) {
                $this->assertEquals(['1', '2'], array_column($steps, 0));
                $this->assertEquals([
                    'From home page, choose a random product and add it to cart',
                    'From home page, open checkout page',
                ], array_column($steps, 1));
                $column3 = array_column($steps, 2);
                $this->assertContains(json_decode($column3[0])->product, $productsOutOfStock);
                $this->assertEquals([], json_decode($column3[1], true));
            }
            elseif (count($steps) === 3 && $steps[1][0] === 'From home page, choose a random product and open it') {
                $this->assertEquals(['1', '2', '3'], array_column($steps, 0));
                $this->assertEquals([
                    'From home page, choose a random product and open it',
                    'From product page, add it to cart',
                    'From product page, open checkout page',
                ], array_column($steps, 1));
                $column3 = array_column($steps, 2);
                $this->assertContains(json_decode($column3[0])->product, $productsOutOfStock);
                $this->assertEquals([], json_decode($column3[1], true));
                $this->assertEquals([], json_decode($column3[2], true));
            }
            elseif (count($steps) === 3 && $steps[1][0] === 'From home page, choose a random category and open it') {
                $this->assertEquals(['1', '2', '3'], array_column($steps, 0));
                $this->assertEquals([
                    'From home page, choose a random category and open it',
                    'From category page, choose a random product and add it to cart',
                    'From category page, open checkout page',
                ], array_column($steps, 1));
                $column3 = array_column($steps, 2);
                $this->assertArrayHasKey('category', json_decode($column3[0], true));
                $this->assertContains(json_decode($column3[1])->product, $productsOutOfStock);
                $this->assertEquals([], json_decode($column3[2], true));
            }
            else {
                $lastStep = end($steps);
                $this->assertContains('open checkout page', $lastStep[1]);
            }
        }
        else {
            // There are no bugs found.
            $this->addToAssertionCount(1);
        }
    }
}
