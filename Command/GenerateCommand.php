<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Traversal\TraversalFactory;
use Tienvx\Bundle\MbtBundle\Exception\ModelNotFoundException;

class GenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbt:generate')
            ->setDescription('Generate test sequence from a model using a specific traversal.')
            ->setHelp('This command allows you to generate test sequence without actually testing the system.')
            ->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'The model to generate.')
            ->addOption('traversal', 't', InputOption::VALUE_OPTIONAL, 'The way to traverse through model to generate test sequence.', 'random(100,100)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $model = $input->getOption('model');
        $workflow = $this->getContainer()->get("workflow.{$model}");
        if (!$workflow instanceof Workflow) {
            $message = sprintf('Can not load model by id "%s".', $model);
            throw new ModelNotFoundException($message);
        }

        $output->writeln([
            sprintf('Generated test sequence for model "%s"', $model),
            '============',
            '',
        ]);

        $progress = new ProgressBar($output);

        $traversalOption = $input->getOption('traversal');
        $traversal = TraversalFactory::create($traversalOption);
        $traversal->setWorkflow($workflow);
        $traversal->setProgress($progress);
        $result = $traversal->run();

        $output->writeln($result);
    }
}
