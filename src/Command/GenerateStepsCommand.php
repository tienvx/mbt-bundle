<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Model\Constants;
use Tienvx\Bundle\MbtBundle\Service\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\StopConditionManager;

class GenerateStepsCommand extends Command
{
    private $modelRegistry;
    private $generatorManager;
    private $stopConditionManager;

    public function __construct(
        ModelRegistry $modelRegistry,
        GeneratorManager $generatorManager,
        StopConditionManager $stopConditionManager)
    {
        $this->modelRegistry = $modelRegistry;
        $this->generatorManager = $generatorManager;
        $this->stopConditionManager = $stopConditionManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:generate-steps')
            ->setDescription('Generate steps from model.')
            ->setHelp('Generate steps from model. So that it can be run with mbt:run-steps command.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to generate.')
            ->addOption('generator', 'g', InputOption::VALUE_OPTIONAL, 'The generator to generate steps from the model.', Constants::DEFAULT_GENERATOR)
            ->addOption('stop-condition', 's', InputOption::VALUE_OPTIONAL, 'When generator stop generate steps.', Constants::DEFAULT_STOP_CONDITION)
            ->addOption('stop-condition-arguments', 'a', InputOption::VALUE_OPTIONAL, 'The arguments of the stop condition.', Constants::DEFAULT_STOP_CONDITION_ARGUMENTS);
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
        $subject = $model->createSubject(true);
        $stopCondition = $this->stopConditionManager->getStopCondition($input->getOption('stop-condition'));
        $stopCondition->setArguments(json_decode($input->getOption('stop-condition-arguments'), true));

        $generator->init($model, $subject, $stopCondition);

        while (!$generator->meetStopCondition() && $edge = $generator->getNextStep()) {
            $generator->goToNextStep($edge);
        }

        $path = $generator->getPath();
        $output->writeln((string) $path);
    }
}
