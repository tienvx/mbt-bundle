<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Fhaculty\Graph\Edge\Directed;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Exception\ModelNotFoundException;
use Tienvx\Bundle\MbtBundle\Model\Model;
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
        $modelArgument = $input->getArgument('model');
        $model = $this->getContainer()->get("model.{$modelArgument}");
        if (!$model instanceof Model) {
            $message = sprintf('Can not load model by id "%s".', $modelArgument);
            throw new ModelNotFoundException($message);
        }

        $traversalOption = $input->getOption('traversal');
        $traversal = TraversalFactory::create($traversalOption, $model);

        $progress = new ProgressBar($output);
        $progress->setMessage(sprintf('Testing system defined by model "%s"', $modelArgument));
        $progress->start($traversal->getMaxProgress());

        try {
            while (!$traversal->meetStopCondition() && $traversal->hasNextStep()) {
                if ($traversal->canGoNextStep()) {
                    $traversal->goToNextStep(true);
                    $progress->setMessage($traversal->getCurrentProgressMessage());
                    $progress->setProgress($traversal->getCurrentProgress());
                }
            }
        }
        catch (\Throwable $throwable) {
            $output->writeln([
                'Found a bug: ' . $throwable,
            ]);
        }
        finally {
            $progress->finish();
        }
    }
}
