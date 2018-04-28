<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Command\TestCommand;
use Tienvx\Bundle\MbtBundle\Service\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\PathReducerManager;

class TestCommandTest extends CommandTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $modelRegistry = self::$container->get(ModelRegistry::class);
        $generatorManager = self::$container->get(GeneratorManager::class);
        $pathReducerManager = self::$container->get(PathReducerManager::class);

        $application = new Application($kernel);
        $application->add(new TestCommand($modelRegistry, $generatorManager, $pathReducerManager));

        $command = $application->find('mbt:test');
        $this->assertReproducePath($command, 'random', $this->getCoverageStopCondition(100, 100));
        $this->assertReproducePath($command, 'random', $this->getFoundBugStopCondition());
        $this->assertReproducePath($command, 'all-places', null);
        $this->assertReproducePath($command, 'all-transitions', null);
    }

    public function assertReproducePath(Command $command, string $generator, $arguments)
    {
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'      => $command->getName(),
            'model'      => 'shopping_cart',
            '--generator'  => $generator,
            '--arguments'  => $arguments
        ]);

        $output = $commandTester->getDisplay();
        if (!empty($output)) {
            $this->assertContains('Found a bug: You added an out-of-stock product into cart! Can not checkout', $output);

            // Assert steps.
            $steps = [];
            $lines = explode("\n", $output);
            foreach ($lines as $line) {
                if (strpos($line, '|') === 0) {
                    // Table rows.
                    $cells = explode('|', $line);
                    $cells = array_map('trim', array_values(array_filter($cells)));
                    if (['Step', 'Label', 'Data Input'] !== $cells) {
                        // Not table header row.
                        $step = [];
                        foreach ($cells as $cell) {
                            $step[] = $cell;
                        }
                        $steps[] = $step;
                    }
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
