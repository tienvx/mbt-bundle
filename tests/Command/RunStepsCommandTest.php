<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Command\RunStepsCommand;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\PathReducerManager;
use Tienvx\Bundle\MbtBundle\Service\PathRunner;

class RunStepsCommandTest extends CommandTestCase
{
    public function testRun()
    {
        $kernel = static::bootKernel();

        /** @var ModelRegistry $modelRegistry */
        $modelRegistry = self::$container->get(ModelRegistry::class);
        /** @var GraphBuilder $graphBuilder */
        $graphBuilder = self::$container->get(GraphBuilder::class);
        /** @var PathRunner $pathRunner */
        $pathRunner = self::$container->get(PathRunner::class);
        /** @var PathReducerManager $pathReducerManager */
        $pathReducerManager = self::$container->get(PathReducerManager::class);

        $application = new Application($kernel);
        $application->add(new RunStepsCommand($modelRegistry, $graphBuilder, $pathRunner, $pathReducerManager));

        $command = $application->find('mbt:run-steps');
        $output = $this->runCommand($command, 'home viewProductFromHome(product=49) product addFromProduct() product viewCartFromProduct() cart');
        $this->assertEquals('', $output);
        $output = $this->runCommand($command, 'home viewAnyCategoryFromHome(category=24) category addFromCategory(product=29) category viewProductFromCategory(product=40) product checkoutFromProduct() checkout');
        $this->assertEquals('', $output);
        $output = $this->runCommand($command, 'home addFromHome(product=49) home viewAnyCategoryFromHome(category=33) category addFromCategory(product=31) category viewCartFromCategory() cart update(product=31) cart remove(product=31) cart checkoutFromCart() checkout backToHomeFromCheckout() home');
        $this->assertContains('Found a bug: You added an out-of-stock product into cart! Can not checkout', $output);
        $output = $this->runCommand($command, 'home addFromHome(product=40) home viewAnyCategoryFromHome(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout', 'greedy');
        $this->assertEquals('Found a bug: You added an out-of-stock product into cart! Can not checkout
+--------+----------------------------------------------------------------+-------------------+
| Steps to reproduce                                                                          |
+--------+----------------------------------------------------------------+-------------------+
| Step   | Action                                                         | Data              |
+--------+----------------------------------------------------------------+-------------------+
| 1      | From home page, choose a random category and open it           | {"category":"57"} |
| 2      | From category page, choose a random product and add it to cart | {"product":"49"}  |
| 3      | From category page, open checkout page                         | []                |
+--------+----------------------------------------------------------------+-------------------+
', $output);
        $output = $this->runCommand($command, 'home viewAnyCategoryFromHome(category=33) category addFromCategory(product=31) category viewCartFromCategory() cart backToHomeFromCart() home viewAnyCategoryFromHome(category=57) category viewProductFromCategory(product=49) product addFromProduct() product checkoutFromProduct() checkout', 'binary');
        $this->assertEquals('Found a bug: You added an out-of-stock product into cart! Can not checkout
+--------+---------------------------------------------------------+-------------------+
| Steps to reproduce                                                                   |
+--------+---------------------------------------------------------+-------------------+
| Step   | Action                                                  | Data              |
+--------+---------------------------------------------------------+-------------------+
| 1      | From home page, choose a random category and open it    | {"category":"57"} |
| 2      | From category page, choose a random product and open it | {"product":"49"}  |
| 3      | From product page, add it to cart                       | []                |
| 4      | From product page, open checkout page                   | []                |
+--------+---------------------------------------------------------+-------------------+
', $output);
        $output = $this->runCommand($command, 'home viewAnyCategoryFromHome(category=34) category viewOtherCategory(category=57) category addFromCategory(product=49) category viewOtherCategory(category=34) category viewProductFromCategory(product=48) product backToHomeFromProduct() home checkoutFromHome() checkout', 'binary');
        $this->assertEquals('Found a bug: You added an out-of-stock product into cart! Can not checkout
+--------+----------------------------------------------------------------+-------------------+
| Steps to reproduce                                                                          |
+--------+----------------------------------------------------------------+-------------------+
| Step   | Action                                                         | Data              |
+--------+----------------------------------------------------------------+-------------------+
| 1      | From home page, choose a random category and open it           | {"category":"34"} |
| 2      | From category page, choose a random category and open it       | {"category":"57"} |
| 3      | From category page, choose a random product and add it to cart | {"product":"49"}  |
| 4      | From category page, open checkout page                         | []                |
+--------+----------------------------------------------------------------+-------------------+
', $output);
        $output = $this->runCommand($command, 'home viewCartFromHome() cart backToHomeFromCart() home viewAnyCategoryFromHome(category=57) category addFromCategory(product=49) category viewOtherCategory(category=25_28) category viewOtherCategory(category=20) category checkoutFromCategory() checkout', 'greedy');
        $this->assertEquals('Found a bug: You added an out-of-stock product into cart! Can not checkout
+--------+----------------------------------------------------------------+-------------------+
| Steps to reproduce                                                                          |
+--------+----------------------------------------------------------------+-------------------+
| Step   | Action                                                         | Data              |
+--------+----------------------------------------------------------------+-------------------+
| 1      | From home page, choose a random category and open it           | {"category":"57"} |
| 2      | From category page, choose a random product and add it to cart | {"product":"49"}  |
| 3      | From category page, open checkout page                         | []                |
+--------+----------------------------------------------------------------+-------------------+
', $output);
        $output = $this->runCommand($command, 'home viewAnyCategoryFromHome(category=34) category viewOtherCategory(category=57) category addFromCategory(product=49) category viewOtherCategory(category=34) category viewProductFromCategory(product=48) product backToHomeFromProduct() home checkoutFromHome() checkout', 'greedy');
        if (strpos($output, '{"category":"34"}')) {
            $this->assertEquals('Found a bug: You added an out-of-stock product into cart! Can not checkout
+--------+----------------------------------------------------------------+-------------------+
| Steps to reproduce                                                                          |
+--------+----------------------------------------------------------------+-------------------+
| Step   | Action                                                         | Data              |
+--------+----------------------------------------------------------------+-------------------+
| 1      | From home page, choose a random category and open it           | {"category":"34"} |
| 2      | From category page, choose a random category and open it       | {"category":"57"} |
| 3      | From category page, choose a random product and add it to cart | {"product":"49"}  |
| 4      | From category page, open checkout page                         | []                |
+--------+----------------------------------------------------------------+-------------------+
', $output);
        }
        else {
            $this->assertEquals('Found a bug: You added an out-of-stock product into cart! Can not checkout
+--------+----------------------------------------------------------------+-------------------+
| Steps to reproduce                                                                          |
+--------+----------------------------------------------------------------+-------------------+
| Step   | Action                                                         | Data              |
+--------+----------------------------------------------------------------+-------------------+
| 1      | From home page, choose a random category and open it           | {"category":"57"} |
| 2      | From category page, choose a random product and add it to cart | {"product":"49"}  |
| 3      | From category page, open checkout page                         | []                |
+--------+----------------------------------------------------------------+-------------------+
', $output);
        }
    }

    public function runCommand(Command $command, $steps, $reducer = null)
    {
        $commandTester = new CommandTester($command);
        $input = [
            'command'        => $command->getName(),
            'model'          => 'shopping_cart',
            'steps'          => $steps,
            '--reducer'      => $reducer,
        ];
        $commandTester->execute($input);

        $output = $commandTester->getDisplay();

        return $output;
    }
}
