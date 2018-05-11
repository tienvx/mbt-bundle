<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Constants;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\PathReducerManager;
use Tienvx\Bundle\MbtBundle\Service\PathRunner;

class RunStepsCommand extends Command
{
    use BugOutputTrait;

    private $modelRegistry;
    private $graphBuilder;
    private $pathRunner;
    private $pathReducerManager;

    public function __construct(
        ModelRegistry $modelRegistry,
        GraphBuilder $graphBuilder,
        PathRunner $pathRunner,
        PathReducerManager $pathReducerManager)
    {
        $this->modelRegistry      = $modelRegistry;
        $this->graphBuilder       = $graphBuilder;
        $this->pathRunner         = $pathRunner;
        $this->pathReducerManager = $pathReducerManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:run-steps')
            ->setDescription('Run steps.')
            ->setHelp('Run steps. The steps can be generated from mbt:generate-steps command.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to run.')
            ->addArgument('steps', InputArgument::REQUIRED, 'The steps to run.')
            ->addOption('reducer', 'r', InputOption::VALUE_OPTIONAL, 'The path reducer to reduce the steps.', Constants::DEFAULT_REDUCER);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $model = $this->modelRegistry->getModel($input->getArgument('model'));
        $graph = $this->graphBuilder->build($model->getDefinition());
        $path = Path::fromSteps($input->getArgument('steps'), $graph);

        try {
            $this->pathRunner->run($path, $model);
        }
        catch (Throwable $throwable) {
            $reducer = $input->getOption('reducer');
            if ($reducer) {
                $pathReducer = $this->pathReducerManager->getPathReducer($reducer);
                $path = $pathReducer->reduce($path, $model, $throwable);
            }

            $this->printBug($throwable->getMessage(), $path, $output);
        }
    }
}
