<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Command\RunCommand;

class RunCommandTest extends KernelTestCase
{
    public function testRun()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $modelRegistry = $kernel->getContainer()->get('Tienvx\Bundle\MbtBundle\Service\ModelRegistry.test');
        $graphBuilder = $kernel->getContainer()->get('Tienvx\Bundle\MbtBundle\Service\GraphBuilder.test');
        $pathRunner = $kernel->getContainer()->get('Tienvx\Bundle\MbtBundle\Service\PathRunner.test');
        $pathReducer = $kernel->getContainer()->get('Tienvx\Bundle\MbtBundle\Service\PathReducer.test');

        $application = new Application($kernel);
        $application->add(new RunCommand($modelRegistry, $graphBuilder, $pathRunner, $pathReducer));

        $command = $application->find('mbt:run');
        $output = $this->runCommand($command, 'home viewProductFromHome(product=28) product addFromProduct() product viewCartFromProduct() cart', false);
        $this->assertEquals('', $output);
        $output = $this->runCommand($command, 'home viewAnyCategoryFromHome(category=24) category addFromCategory(product=29) category viewProductFromCategory(product=40) product checkoutFromProduct() checkout', false);
        $this->assertEquals('', $output);
        $output = $this->runCommand($command, 'home addFromHome(product=28) home viewAnyCategoryFromHome(category=33) category addFromCategory(product=31) category viewCartFromCategory() cart update(product=31) cart remove(product=31) cart checkoutFromCart() checkout backToHomeFromCheckout() home', false);
        $this->assertContains('Found a bug: You added an out-of-stock product into cart! Can not checkout', $output);
        $output = $this->runCommand($command, 'home addFromHome(product=29) home addFromHome(product=43) home addFromHome(product=40) home checkoutFromHome() checkout', true);
        $this->assertEquals('Found a bug: You added an out-of-stock product into cart! Can not checkout
Steps to reproduce:
+------+------------------------------------------------------------+------------------+
| Step | Label                                                      | Data Input       |
+------+------------------------------------------------------------+------------------+
| 1    | From home page, choose a random product and add it to cart | {"product":"40"} |
| 2    | From home page, open checkout page                         | []               |
+------+------------------------------------------------------------+------------------+
', $output);
    }

    public function runCommand(Command $command, $steps, $reduce)
    {
        $commandTester = new CommandTester($command);
        $input = [
            'command'        => $command->getName(),
            'model'          => 'shopping_cart',
            'steps'          => $steps,
            '--reduce'       => $reduce,
        ];
        $commandTester->execute($input);

        $output = $commandTester->getDisplay();

        return $output;
    }
}
