<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Fhaculty\Graph\Edge\Directed;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Exception\ModelNotFoundException;
use Tienvx\Bundle\MbtBundle\Tests\App\Entity\ShoppingCart;
use Tienvx\Bundle\MbtBundle\Traversal\TraversalFactory;

class TestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbt:test')
            ->setDescription('Test system defined by a model using a specific traversal then report bug if found.')
            ->setHelp('This command test the system step by step defined by a model using a specific traversal, then report bug if found.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to test.')
            ->addOption('traversal', 't', InputOption::VALUE_OPTIONAL, 'The way to traverse through model to generate test sequence to test.', 'random(100,100)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $model = $input->getArgument('model');
        $workflow = $this->getContainer()->get("state_machine.{$model}");
        if (!$workflow instanceof Workflow) {
            $message = sprintf('Can not load model by id "%s".', $model);
            throw new ModelNotFoundException($message);
        }

        $traversalOption = $input->getOption('traversal');
        $traversal = TraversalFactory::create($traversalOption);
        $traversal->setWorkflow($workflow);
        $traversal->init();

        $progress = new ProgressBar($output);
        $progress->setMessage(sprintf('Testing system defined by model "%s"', $model));
        $progress->start($traversal->getMaxProgress());

        $shoppingCart = new ShoppingCart();
        $testSequence = [];
        $testSequence[] = $traversal->getCurrentVertex()->getAttribute('text');

        try {
            while (!$traversal->meetStopCondition() && $traversal->hasNextStep()) {
                /** @var Directed $edge */
                $edge = $traversal->getNextStep();
                if ($workflow->can($shoppingCart, $edge->getAttribute('name'))) {
                    $testSequence[] = $edge->getAttribute('text');
                    $traversal->goToNextStep($edge);
                    $workflow->apply($shoppingCart, $edge->getAttribute('name'));
                    $testSequence[] = $traversal->getCurrentVertex()->getAttribute('text');
                    $progress->setMessage($traversal->getCurrentProgressMessage());
                    $progress->setProgress($traversal->getCurrentProgress());
                }
            }
        }
        catch (\Exception $exception) {
            $output->writeln([
                'Found a bug:',
                sprintf('Message: "%s"', $exception->getMessage()),
                sprintf('Code: "%s"', $exception->getCode()),
            ]);
        }
        finally {
            $progress->finish();
        }
    }
}
