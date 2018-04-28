<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Fhaculty\Graph\Edge\Directed;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Registry;
use Throwable;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\PathReducerManager;
use Tienvx\Bundle\MbtBundle\Service\PathRunner;

class RunCommand extends Command
{
    private $modelRegistry;
    private $graphBuilder;
    private $pathRunner;
    private $pathReducerManager;
    private $workflows;

    public function __construct(ModelRegistry $modelRegistry, GraphBuilder $graphBuilder, PathRunner $pathRunner, PathReducerManager $pathReducerManager, Registry $workflows)
    {
        $this->modelRegistry      = $modelRegistry;
        $this->graphBuilder       = $graphBuilder;
        $this->pathRunner         = $pathRunner;
        $this->pathReducerManager = $pathReducerManager;
        $this->workflows          = $workflows;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:run')
            ->setDescription('Run test sequence generated from mbt:generate command.')
            ->setHelp('This command allows you to run test sequence that is generated from mbt:generate command.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to run.')
            ->addArgument('steps', InputArgument::REQUIRED, 'The test steps to run.')
            ->addOption('reducer', 'r', InputOption::VALUE_OPTIONAL, 'The way to reduce the reproduce path.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $model = $input->getArgument('model');
        $workflowMetadata = $this->modelRegistry->getModel($model);
        $subject = $workflowMetadata['subject'];
        $workflow = $this->workflows->get(new $subject(), $model);

        $graph = $this->graphBuilder->build($workflow->getDefinition());

        $edges = [];
        $vertices = [];
        $allData = [];
        $steps = $input->getArgument('steps');
        $steps = explode(' ', $steps);
        foreach ($steps as $index => $step) {
            if (preg_match('/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\((.*)\)/', $step, $matches)) {
                $transition = $matches[1];
                $data = [];
                if ($matches[2]) {
                    $params = explode(',', $matches[2]);
                    foreach ($params as $param) {
                        list($key, $value) = explode('=', $param);
                        $data[$key] = $value;
                    }
                }
                $edge = $graph->getEdges()->getEdgeMatch(function (Directed $edge) use ($transition) {
                    return $edge->getAttribute('name') === $transition;
                });
                $allData[] = $data;
                $edges[] = $edge;
            }
            else {
                $vertex = $graph->getVertex($step);
                $vertices[] = $vertex;
            }
        }
        $path = new Path($vertices, $edges, $allData);

        try {
            $this->pathRunner->run($path, $model, $subject);
        }
        catch (Throwable $throwable) {
            $output->writeln('Found a bug: ' . $throwable->getMessage());

            $reducer = $input->getOption('reducer');
            if ($reducer) {
                $pathReducer = $this->pathReducerManager->getPathReducer($reducer);
                $path = $pathReducer->reduce($path, $model, $subject, $throwable);
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
