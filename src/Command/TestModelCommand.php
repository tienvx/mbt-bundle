<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorArgumentsTrait;
use Tienvx\Bundle\MbtBundle\Service\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\PathReducerManager;

class TestModelCommand extends Command
{
    use GeneratorArgumentsTrait;
    use BugOutputTrait;

    private $modelRegistry;
    private $generatorManager;
    private $pathReducerManager;
    private $defaultGenerator;
    private $defaultArguments;
    private $defaultReducer;

    public function __construct(
        ModelRegistry $modelRegistry,
        GeneratorManager $generatorManager,
        PathReducerManager $pathReducerManager,
        string $defaultGenerator,
        string $defaultArguments,
        string $defaultReducer)
    {
        $this->modelRegistry = $modelRegistry;
        $this->generatorManager = $generatorManager;
        $this->pathReducerManager = $pathReducerManager;
        $this->defaultGenerator = $defaultGenerator;
        $this->defaultArguments = $defaultArguments;
        $this->defaultReducer = $defaultReducer;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:test-model')
            ->setDescription('Test a model.')
            ->setHelp('Test a model. This command is combined by mbt:generate-steps and mbt:run-steps commands.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to test.')
            ->addOption('generator', 'g', InputOption::VALUE_OPTIONAL, 'The generator to generate steps from the model.', $this->defaultGenerator)
            ->addOption('arguments', 'a', InputOption::VALUE_OPTIONAL, 'The arguments of the generator.', $this->defaultArguments)
            ->addOption('reducer', 'r', InputOption::VALUE_OPTIONAL, 'The path reducer to reduce the steps.', $this->defaultReducer);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $model = $input->getArgument('model');
        $generator = $this->generatorManager->getGenerator($input->getOption('generator'));
        $workflowMetadata = $this->modelRegistry->getModel($model);
        $subject = $workflowMetadata['subject'];
        $arguments = $this->parseGeneratorArguments($input->getOption('arguments'));

        $generator->init($model, $subject, $arguments);

        try {
            while (!$generator->meetStopCondition() && $edge = $generator->getNextStep()) {
                if ($generator->canGoNextStep($edge)) {
                    $generator->goToNextStep($edge);
                }
            }
        }
        catch (Throwable $throwable) {
            $path = $generator->getPath();
            $reducer = $input->getOption('reducer');
            if ($reducer) {
                $pathReducer = $this->pathReducerManager->getPathReducer($reducer);
                $path = $pathReducer->reduce($path, $model, $subject, $throwable);
            }

            $this->printBug($throwable->getMessage(), $path, $output);
        }
    }
}
