<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Exception\ModelNotFoundException;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Traversal\TraversalFactory;

class GenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbt:generate')
            ->setDescription('Generate test sequence from a model using a specific traversal.')
            ->setHelp('This command allows you to generate test sequence without actually testing the system.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to generate.')
            ->addOption('traversal', 't', InputOption::VALUE_OPTIONAL, 'The way to traverse through model to generate test sequence.', 'random(100,100)');
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
        $progress->setMessage(sprintf('Generating test sequence for model "%s"', $modelArgument));
        $progress->start($traversal->getMaxProgress());

        while (!$traversal->meetStopCondition() && $traversal->hasNextStep()) {
            if ($traversal->canGoNextStep()) {
                $traversal->goToNextStep();
                $progress->setMessage($traversal->getCurrentProgressMessage());
                $progress->setProgress($traversal->getCurrentProgress());
            }
        }
        $progress->finish();

        $output->writeln([
            '===Begin generated test sequence===',
            implode(' ', $traversal->getTestSequence()),
            '===End generated test sequence==='
        ]);
    }
}
