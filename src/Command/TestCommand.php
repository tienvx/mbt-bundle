<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Fhaculty\Graph\Edge\Directed;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Service\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\PathReducerManager;

class TestCommand extends Command
{
    use ModelArgumentsTrait;

    private $modelRegistry;
    private $generatorManager;
    private $pathReducerManager;

    public function __construct(ModelRegistry $modelRegistry, GeneratorManager $generatorManager, PathReducerManager $pathReducerManager)
    {
        $this->modelRegistry = $modelRegistry;
        $this->generatorManager = $generatorManager;
        $this->pathReducerManager = $pathReducerManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:test')
            ->setDescription('Test system defined by a model using a specific generator then report bug if found.')
            ->setHelp('This command test the system step by step defined by a model using a specific generator, then report bug if found.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to test.')
            ->addOption('generator', 'g', InputOption::VALUE_OPTIONAL, 'The way to generate test sequence from model to test.', 'random')
            ->addOption('arguments', 'a', InputOption::VALUE_OPTIONAL, 'The arguments pass to generator.', '{"stop":{"on":"coverage","at":{"edgeCoverage":100,"vertexCoverage":100}}}')
            ->addOption('reducer', 'r', InputOption::VALUE_OPTIONAL, 'The way to reduce the reproduce path.', 'weighted-random');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $generator = $this->generatorManager->getGenerator($input->getOption('generator'));
        $model = $this->modelRegistry->get($input->getArgument('model'));
        $arguments = $this->parseModelArguments($input->getOption('arguments'));

        $generator->init($model, $arguments);

        try {
            while (!$generator->meetStopCondition() && $edge = $generator->getNextStep()) {
                if ($generator->canGoNextStep($edge)) {
                    $generator->goToNextStep($edge, true);
                }
            }
        }
        catch (Throwable $throwable) {
            $output->writeln('Found a bug: ' . $throwable->getMessage());

            $path = $generator->getPath();
            $reducer = $input->getOption('reducer');
            if ($reducer) {
                $pathReducer = $this->pathReducerManager->getPathReducer($reducer);
                $path = $pathReducer->reduce($path, $model, $throwable);
            }

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
