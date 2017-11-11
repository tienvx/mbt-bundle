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
        $productsAddedToCart = [];
        $productsOutOfStock = [28, 40, 41, 33];
        foreach ($steps as $index => $step) {
            if (strpos($step[1], 'add it to cart') !== false) {
                if ($step[1] === 'From product page, add it to cart') {
                    $data = json_decode($steps[$index - 1][2], true);
                }
                else {
                    $data = json_decode($step[2], true);
                }
                $this->assertArrayHasKey('product', $data);
                if (!in_array((int) $data['product'], $productsAddedToCart)) {
                    $productsAddedToCart[] = (int) $data['product'];
                }
            }
            else if (strpos($step[1], 'remove it') !== false) {
                $data = json_decode($step[2], true);
                $this->assertArrayHasKey('product', $data);
                $productsAddedToCart = array_diff($productsAddedToCart, [(int) $data['product']]);
            }
        }
        $productsOutOfStockAddedToCart = array_intersect($productsAddedToCart, $productsOutOfStock);
        $this->assertNotEmpty($productsOutOfStockAddedToCart);
        $lastStep = end($steps);
        $this->assertContains('open checkout page', $lastStep[1]);
    }
}
