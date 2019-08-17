<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Entity\Step;
use Tienvx\Bundle\MbtBundle\Entity\Data;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class TestModelCommand extends AbstractCommand
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
        GeneratorManager $generatorManager
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->subjectManager = $subjectManager;
        $this->generatorManager = $generatorManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:model:test')
            ->setDescription('Generate path for model.')
            ->setHelp('Generate path for model.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to test.')
            ->addOption('generator', 'g', InputOption::VALUE_OPTIONAL, 'The generator to generate path from the model.', 'random')
            ->addOption('generator-options', 'o', InputOption::VALUE_OPTIONAL, 'The options for the generator.')
            ->addOption('pretty', 'p', InputOption::VALUE_NONE, 'Whether print json in pretty format.', null);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $model = $input->getArgument('model');
        $subject = $this->getSubject($model);
        $workflow = WorkflowHelper::get($this->workflowRegistry, $model);
        $pretty = $input->getOption('pretty');
        $generator = $this->generatorManager->getGenerator($input->getOption('generator'));
        $generatorOptions = GeneratorOptions::denormalize($input->getOption('generator-options'));

        $this->setAnonymousToken();

        $steps = new Steps();
        $steps->addStep(new Step(null, new Data(), $workflow->getDefinition()->getInitialPlaces()));

        try {
            foreach ($generator->generate($workflow, $subject, $generatorOptions) as $step) {
                if ($step instanceof Step && $step->getTransition() && $step->getData() instanceof Data) {
                    try {
                        $workflow->apply($subject, $step->getTransition(), [
                            'data' => $step->getData(),
                        ]);
                    } finally {
                        $places = array_keys(array_filter($workflow->getMarking($subject)->getPlaces()));
                        $step->setPlaces($places);
                        $steps->addStep($step);
                    }
                }
            }
        } finally {
            $subject->tearDown();
        }

        $output->writeln($steps->serialize($pretty ? JSON_PRETTY_PRINT : 0));
    }

    /**
     * @param string $model
     *
     * @return AbstractSubject
     *
     * @throws Exception
     */
    private function getSubject(string $model): AbstractSubject
    {
        $subject = $this->subjectManager->createSubject($model);
        $subject->setTestingModel(true);
        $subject->setUp();

        return $subject;
    }
}
