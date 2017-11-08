<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Command\GenerateCommand;

class TestCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new GenerateCommand());

        $command = $application->find('mbt:test');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'      => $command->getName(),
            'model'      => 'shopping_cart',
            '--traversal'  => "random(100,100)"
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('Found a bug: You added an out-of-stock product into cart! It can not be updated', $output);

        // Assert steps.
        $steps = [];
        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            if (strpos($line, '|') === 0) {
                // Table rows.
                $cells = explode('|', $line);
                $cells = array_map('trim', array_values(array_filter($cells)));
                if (['Step', 'Label', 'Data'] !== $cells) {
                    // Not table header row.
                    $step = [];
                    foreach ($cells as $cell) {
                        $step[] = $cell;
                    }
                    $steps[] = $step;
                }
            }
        }
        if (count($steps) === 3) {
            $this->assertEquals(['1', '2', '3'], array_column($steps, 0));
            $this->assertEquals([
                'From home page, choose a random product and add it to cart',
                'From home page, open cart to view it',
                'From cart page, choose a random product and update it with a random number from 1 to 99',
            ], array_column($steps, 1));
            $column3 = array_column($steps, 2);
            $this->assertArrayHasKey('product', json_decode($column3[0], true));
            $this->assertEquals([], json_decode($column3[1], true));
            $this->assertArrayHasKey('product', json_decode($column3[2], true));
        }
        if (count($steps) === 4 && $steps[0][1] === 'From home page, choose a random category and open it') {
            $this->assertEquals(['1', '2', '3', '4'], array_column($steps, 0));
            $this->assertEquals([
                'From home page, choose a random category and open it',
                'From category page, choose a random product and add it to cart',
                'From category page, open cart to view it',
                'From cart page, choose a random product and update it with a random number from 1 to 99',
            ], array_column($steps, 1));
            $column3 = array_column($steps, 2);
            $this->assertArrayHasKey('category', json_decode($column3[0], true));
            $this->assertArrayHasKey('product', json_decode($column3[1], true));
            $this->assertEquals([], json_decode($column3[2], true));
            $this->assertArrayHasKey('product', json_decode($column3[3], true));
        }
        if (count($steps) === 4 && $steps[0][1] === 'From home page, choose a random product and open it') {
            $this->assertEquals(['1', '2', '3', '4'], array_column($steps, 0));
            $this->assertEquals([
                'From home page, choose a random product and open it',
                'From product page, add it to cart',
                'From product page, open cart to view it',
                'From cart page, choose a random product and update it with a random number from 1 to 99',
            ], array_column($steps, 1));
            $column3 = array_column($steps, 2);
            $this->assertArrayHasKey('product', json_decode($column3[0], true));
            $this->assertEquals([], json_decode($column3[1], true));
            $this->assertEquals([], json_decode($column3[2], true));
            $this->assertArrayHasKey('product', json_decode($column3[3], true));
        }
        if (count($steps) === 5) {
            $this->assertEquals(['1', '2', '3', '4', '5'], array_column($steps, 0));
            $this->assertEquals([
                'From home page, choose a random category and open it',
                'From category page, choose a random product and open it',
                'From product page, add it to cart',
                'From product page, open cart to view it',
                'From cart page, choose a random product and update it with a random number from 1 to 99',
            ], array_column($steps, 1));
            $column3 = array_column($steps, 2);
            $this->assertArrayHasKey('category', json_decode($column3[0], true));
            $this->assertArrayHasKey('product', json_decode($column3[1], true));
            $this->assertEquals([], json_decode($column3[2], true));
            $this->assertEquals([], json_decode($column3[3], true));
            $this->assertArrayHasKey('product', json_decode($column3[4], true));
        }
    }
}
