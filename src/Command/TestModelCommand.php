<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Entity\Path;
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
        SubjectManager $subjectManager,
        GeneratorManager $generatorManager
    ) {
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

    public function setWorkflowRegistry(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->workflowRegistry instanceof Registry) {
            throw new Exception('Can not test model: No workflows were defined');
        }

        $this->setAnonymousToken();

        $model = $input->getArgument('model');
        $generatorName = $input->getOption('generator');
        $generatorOptions = $input->getOption('generator-options');
        $pretty = $input->getOption('pretty');
        $generator = $this->generatorManager->getGenerator($generatorName);
        $subject = $this->subjectManager->createSubject($model);
        $subject->setTestingModel(true);
        $subject->setUp();

        try {
            $workflow = $this->workflowRegistry->get($subject, $model);
        } catch (InvalidArgumentException $exception) {
            throw new Exception(sprintf('Model "%s" does not exist', $model));
        }

        $path = new Path();
        $path->addStep([null, null, $workflow->getDefinition()->getInitialPlaces()]);

        try {
            foreach ($generator->getAvailableTransitions($workflow, $subject, GeneratorOptions::denormalize($generatorOptions)) as $transitionName) {
                try {
                    if (!$generator->applyTransition($workflow, $subject, $transitionName)) {
                        throw new Exception(sprintf("Generator '%s' generated transition '%s' that can not be applied", $generatorName, $transitionName));
                    }
                } finally {
                    $data = $subject->getStoredData();
                    $places = array_keys(array_filter($workflow->getMarking($subject)->getPlaces()));
                    $path->addStep([$transitionName, $data, $places]);
                }
            }
        } finally {
            $subject->tearDown();
        }

        $output->writeln($pretty ? json_encode(Path::normalize($path), JSON_PRETTY_PRINT) : Path::serialize($path));
    }
}
