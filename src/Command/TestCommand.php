<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Fhaculty\Graph\Edge\Directed;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Exception\ModelNotFoundException;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\PathReducer;
use Tienvx\Bundle\MbtBundle\Service\TraversalFactory;

class TestCommand extends Command
{
    private $modelRegistry;
    private $traversalFactory;
    private $pathReducer;

    public function __construct(ModelRegistry $modelRegistry, TraversalFactory $traversalFactory, PathReducer $pathReducer)
    {
        $this->modelRegistry = $modelRegistry;
        $this->traversalFactory = $traversalFactory;
        $this->pathReducer = $pathReducer;

        parent::__construct();
    }

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
        $model = $this->modelRegistry->get($modelArgument);
        if (!$model instanceof Model) {
            $message = sprintf('Can not load model by id "%s".', $modelArgument);
            throw new ModelNotFoundException($message);
        }

        $traversalOption = $input->getOption('traversal');
        $traversal = $this->traversalFactory->get($traversalOption, $model);

        try {
            while (!$traversal->meetStopCondition() && $edge = $traversal->getNextStep()) {
                if ($traversal->canGoNextStep($edge)) {
                    $traversal->goToNextStep($edge, true);
                }
            }
        }
        catch (\Throwable $throwable) {
            $output->writeln('Found a bug: ' . $throwable->getMessage());

            $path = $traversal->getPath();
            $path = $this->pathReducer->reduce($path, $model, $throwable);

            $output->writeln('Steps to reproduce:');
            $table = new Table($output);
            $table->setHeaders(array('Step', 'Label', 'Data Input'));
            /** @var Directed[] $edges */
            $edges = $path->getEdges();
            $allData = $path->getAllData();
            foreach ($edges as $index => $edge) {
                $table->addRow([$index + 1, $edge->getAttribute('label'), json_encode($allData[$index])]);
            }
            $table->render();
        }
    }
}
