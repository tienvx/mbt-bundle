<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Command\GenerateCommand;

class ExecuteCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new GenerateCommand());

        $command = $application->find('mbt:execute');
        $this->assertExecution($command, 'home viewProductFromHome(product=28) product addFromProduct() product viewCartFromProduct() cart', 'No bug found');
        $this->assertExecution($command, 'home viewAnyCategoryFromHome(category=24) category addFromCategory(product=28) category viewProductFromCategory(product=40) product checkoutFromProduct() checkout', 'No bug found');
        $this->assertExecution($command, 'home addFromHome(product=30) home viewAnyCategoryFromHome(category=33) category addFromCategory(product=31) category viewCartFromCategory() cart update(product=31) cart remove(product=30) cart checkoutFromCart() checkout', 'Found a bug: Exception: You added an out-of-stock product into cart! It can not be updated');
    }

    public function assertExecution(Command $command, $testSequence, $message)
    {
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'        => $command->getName(),
            'model'          => 'shopping_cart',
            'test-sequence'  => $testSequence
        ]);

        $output = $commandTester->getDisplay();

        $this->assertContains($message, $output);
    }
}
