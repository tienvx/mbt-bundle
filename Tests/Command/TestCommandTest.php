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
        if (!empty($output)) {
            $this->assertReproducePath($output);
        }
        else {
            // There are no bugs found.
            $this->addToAssertionCount(1);
        }
    }

    public function assertReproducePath(string $output)
    {
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
        //$productsOutOfStock = [28, 40, 41, 33];
        $lastStep = end($steps);
        $this->assertContains('open checkout page', $lastStep[1]);
    }
}
