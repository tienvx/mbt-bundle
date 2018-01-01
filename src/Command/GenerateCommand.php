<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Exception\ModelNotFoundException;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\TraversalFactory;

class GenerateCommand extends Command
{
    private $modelRegistry;
    private $traversalFactory;

    public function __construct(ModelRegistry $modelRegistry, TraversalFactory $traversalFactory)
    {
        $this->modelRegistry = $modelRegistry;
        $this->traversalFactory = $traversalFactory;

        parent::__construct();
    }

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
        $model = $this->modelRegistry->get($modelArgument);
        if (!$model instanceof Model) {
            $message = sprintf('Can not load model by id "%s".', $modelArgument);
            throw new ModelNotFoundException($message);
        }

        $traversalOption = $input->getOption('traversal');
        $traversal = $this->traversalFactory->get($traversalOption, $model);

        while (!$traversal->meetStopCondition() && $edge = $traversal->getNextStep()) {
            if ($traversal->canGoNextStep($edge)) {
                $traversal->goToNextStep($edge);
            }
        }

        $path = $traversal->getPath();
        $output->writeln((string) $path);
    }
}
