<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Helper\Constants;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class GenerateStepsCommand extends Command
{
    /**
     * @var Registry
     */
    private $workflowRegistry;

    /**
     * @var SubjectManager
     */
    private $subjectManager;

    /**
     * @var GeneratorManager
     */
    private $generatorManager;

    public function __construct(
        Registry $workflowRegistry,
        SubjectManager $subjectManager,
        GeneratorManager $generatorManager)
    {
        $this->workflowRegistry     = $workflowRegistry;
        $this->subjectManager       = $subjectManager;
        $this->generatorManager     = $generatorManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:generate-steps')
            ->setDescription('Generate steps from model.')
            ->setHelp('Generate steps from model.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to generate.')
            ->addOption('generator', 'g', InputOption::VALUE_OPTIONAL, 'The generator to generate steps from the model.', Constants::DEFAULT_GENERATOR)
            ->addOption('stop-condition', 's', InputOption::VALUE_OPTIONAL, 'When generator stop generate steps.', Constants::DEFAULT_STOP_CONDITION)
            ->addOption('stop-condition-arguments', 'a', InputOption::VALUE_OPTIONAL, 'The arguments of the stop condition.', Constants::DEFAULT_STOP_CONDITION_ARGUMENTS);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $model = $input->getArgument('model');
        $generatorName = $input->getOption('generator');
        $generator = $this->generatorManager->getGenerator($generatorName);
        $subject = $this->subjectManager->createSubjectForModel($model);
        $subject->setTesting(true);
        $subject->setUp();
        $workflow = $this->workflowRegistry->get($subject, $model);

        $path = new Path();

        try {
            foreach ($generator->getAvailableTransitions($workflow, $subject) as $transitionName) {
                $data = $subject->getData();
                $places = array_keys(array_filter($workflow->getMarking($subject)->getPlaces()));
                $path->add($transitionName, $data, $places);
                if (!$generator->applyTransition($workflow, $subject, $transitionName)) {
                    throw new Exception(sprintf('Generator %s generated transition %s that can not be applied', $generatorName, $transitionName));
                }
            }
        } finally {
            $subject->tearDown();
        }

        $output->writeln(serialize($path));
    }
}
