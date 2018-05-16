<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Event\ReducerFinishEvent;
use Tienvx\Bundle\MbtBundle\Model\Constants;
use Tienvx\Bundle\MbtBundle\Service\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\PathReducerManager;
use Tienvx\Bundle\MbtBundle\Service\StopConditionManager;

class TestModelCommand extends Command
{
    use BugOutputTrait;

    private $modelRegistry;
    private $generatorManager;
    private $pathReducerManager;
    private $stopConditionManager;
    private $dispatcher;

    public function __construct(
        ModelRegistry $modelRegistry,
        GeneratorManager $generatorManager,
        PathReducerManager $pathReducerManager,
        StopConditionManager $stopConditionManager,
        EventDispatcherInterface $dispatcher)
    {
        $this->modelRegistry = $modelRegistry;
        $this->generatorManager = $generatorManager;
        $this->pathReducerManager = $pathReducerManager;
        $this->stopConditionManager = $stopConditionManager;
        $this->dispatcher = $dispatcher;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:test-model')
            ->setDescription('Test a model.')
            ->setHelp('Test a model. This command is combined by mbt:generate-steps and mbt:run-steps commands.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to test.')
            ->addOption('generator', 'g', InputOption::VALUE_OPTIONAL, 'The generator to generate steps from the model.', Constants::DEFAULT_GENERATOR)
            ->addOption('stop-condition', 's', InputOption::VALUE_OPTIONAL, 'When generator stop generate steps.', Constants::DEFAULT_STOP_CONDITION)
            ->addOption('stop-condition-arguments', 'a', InputOption::VALUE_OPTIONAL, 'The arguments of the stop condition.', Constants::DEFAULT_STOP_CONDITION_ARGUMENTS)
            ->addOption('reducer', 'r', InputOption::VALUE_OPTIONAL, 'The path reducer to reduce the steps.', Constants::DEFAULT_REDUCER);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $generator = $this->generatorManager->getGenerator($input->getOption('generator'));
        $model = $this->modelRegistry->getModel($input->getArgument('model'));
        $subject = $model->createSubject();
        $stopCondition = $this->stopConditionManager->getStopCondition($input->getOption('stop-condition'));
        $stopCondition->setArguments(json_decode($input->getOption('stop-condition-arguments'), true));

        $generator->init($model, $subject, $stopCondition);

        try {
            while (!$generator->meetStopCondition() && $edge = $generator->getNextStep()) {
                $generator->goToNextStep($edge);
            }
        }
        catch (Throwable $throwable) {
            $path = $generator->getPath();
            $reducer = $input->getOption('reducer');
            if ($reducer) {
                $this->dispatcher->addListener(
                    'tienvx_mbt.reducer.finish',
                    function (ReducerFinishEvent $event) use ($output) {
                        $this->printBug($event->getBugMessage(), $event->getPath(), $output);
                    }
                );

                $pathReducer = $this->pathReducerManager->getPathReducer($reducer);
                $pathReducer->reduce($path, $model, $throwable->getMessage());
            }
            else {
                $this->printBug($throwable->getMessage(), $path, $output);
            }
        }
    }
}
