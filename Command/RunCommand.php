<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Fhaculty\Graph\Edge\Directed;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Exception\ModelNotFoundException;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Service\PathRunner;

class RunCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbt:run')
            ->setDescription('Run test sequence generated from mbt:generate command.')
            ->setHelp('This command allows you to run test sequence that is generated from mbt:generate command.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to run.')
            ->addArgument('steps', InputArgument::REQUIRED, 'The test steps to run.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $modelArgument = $input->getArgument('model');
        $model = $this->getContainer()->get("model.{$modelArgument}");
        if (!$model instanceof Model) {
            $message = sprintf('Can not load model by id "%s".', $modelArgument);
            throw new ModelNotFoundException($message);
        }

        /* @var GraphBuilder $graphBuilder */
        $graphBuilder = $this->getContainer()->get('tienvx_mbt.graph_builder');
        $graph = $graphBuilder->build($model);

        $initialPlace = $model->getDefinition()->getInitialPlace();
        $startVertex = $graph->getVertex($initialPlace);

        $edges = [];
        $steps = $input->getArgument('steps');
        $steps = explode(' ', $steps);
        foreach ($steps as $step) {
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
                $edge->setAttribute('data', $data);
                $edges[] = $edge;
            }
        }
        $path = Path::factoryFromEdges($edges, $startVertex);

        /* @var $runner PathRunner */
        $runner = $this->getContainer()->get('tienvx_mbt.path_runner');
        try {
            $runner->run($path, $model);
        }
        catch (\Throwable $throwable) {
            $output->write('Found a bug: ' . $throwable->getMessage());
        }
    }
}
