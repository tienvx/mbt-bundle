<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Helper\TokenHelper;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Steps\StepsRecorder;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class TestModelCommand extends Command
{
    /**
     * @var SubjectManager
     */
    private $subjectManager;

    /**
     * @var GeneratorManager
     */
    private $generatorManager;

    /**
     * @var TokenHelper
     */
    private $tokenHelper;

    /**
     * @var WorkflowHelper
     */
    private $workflowHelper;

    public function __construct(
        SubjectManager $subjectManager,
        GeneratorManager $generatorManager,
        TokenHelper $tokenHelper,
        WorkflowHelper $workflowHelper
    ) {
        $this->subjectManager = $subjectManager;
        $this->generatorManager = $generatorManager;
        $this->tokenHelper = $tokenHelper;
        $this->workflowHelper = $workflowHelper;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('mbt:model:test')
            ->setDescription('Test model and subject together.')
            ->setHelp('Call system under test to test model and print steps for it.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to test.')
            ->addOption('generator', 'g', InputOption::VALUE_OPTIONAL, 'The generator to generate steps from the model.', 'random')
            ->addOption('generator-options', 'o', InputOption::VALUE_OPTIONAL, 'The options for the generator (in json).', '{}');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $model = $input->getArgument('model');
        $subject = $this->subjectManager->createAndSetUp($model, true);
        $workflow = $this->workflowHelper->get($model);
        $generator = $this->generatorManager->get($input->getOption('generator'));
        $generatorOptions = GeneratorOptions::deserialize($input->getOption('generator-options'));

        $this->tokenHelper->setAnonymousToken();

        $recorded = new Steps();
        try {
            $steps = $generator->generate($workflow, $subject, $generatorOptions);
            StepsRecorder::record($steps, $workflow, $subject, $recorded);
        } catch (Throwable $throwable) {
            $output->writeln([
                sprintf("<comment>There is an issue while testing model '%s':</comment>", $model),
                "<error>{$throwable->getMessage()}</error>",
            ]);
        } finally {
            $subject->tearDown();
        }

        $this->renderTable($output, $recorded);
    }

    protected function renderTable(OutputInterface $output, Steps $recorded): void
    {
        $output->writeln([
            '<info>Testing model is finished! Here are steps:</info>',
        ]);

        $table = new Table($output);
        $table->setHeaders(['Transition', 'Data', 'Places']);
        foreach ($recorded as $step) {
            $table->addRow([$step->getTransition(), $step->getData()->serialize(), implode(', ', $step->getPlaces())]);
        }
        $table->render();
    }
}
