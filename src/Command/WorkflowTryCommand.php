<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Workflow;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Helper\Steps\Recorder as StepsRecorder;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Model\Subject\TearDownInterface;
use Tienvx\Bundle\MbtBundle\Model\SubjectInterface;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class WorkflowTryCommand extends Command
{
    protected static $defaultName = 'mbt:workflow:try';

    /**
     * @var SubjectManager
     */
    private $subjectManager;

    /**
     * @var GeneratorManager
     */
    private $generatorManager;

    /**
     * @var WorkflowHelper
     */
    private $workflowHelper;

    /**
     * @var StepsRecorder
     */
    private $stepsRecorder;

    public function __construct(
        SubjectManager $subjectManager,
        GeneratorManager $generatorManager,
        WorkflowHelper $workflowHelper,
        StepsRecorder $stepsRecorder
    ) {
        $this->subjectManager = $subjectManager;
        $this->generatorManager = $generatorManager;
        $this->workflowHelper = $workflowHelper;
        $this->stepsRecorder = $stepsRecorder;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Try workflow on a new subject.')
            ->setHelp('Create new subject and call system under test to try workflow and print steps for it.')
            ->addArgument('workflow-name', InputArgument::REQUIRED, 'The workflow name to try.')
            ->addOption('generator', 'g', InputOption::VALUE_OPTIONAL, 'The generator name.', 'random')
            ->addOption('max-steps', 's', InputOption::VALUE_OPTIONAL, 'The maximum number of steps.', null)
            ->addOption('transition-coverage', 't', InputOption::VALUE_OPTIONAL, 'The limit of transition coverage.', null)
            ->addOption('place-coverage', 'p', InputOption::VALUE_OPTIONAL, 'The limit of place coverage.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $workflowName = $input->getArgument('workflow-name');
        $subject = $this->subjectManager->create($workflowName, true);
        $workflow = $this->workflowHelper->get($workflowName);
        $generator = $this->generatorManager->get($input->getOption('generator'));
        $generatorOptions = new GeneratorOptions();
        $generatorOptions->setMaxSteps($input->getOption('max-steps'));
        $generatorOptions->setTransitionCoverage($input->getOption('transition-coverage'));
        $generatorOptions->setPlaceCoverage($input->getOption('place-coverage'));

        $this->try($generator, $generatorOptions, $workflow, $subject, $output);

        return 0;
    }

    protected function try(GeneratorInterface $generator, GeneratorOptions $generatorOptions, Workflow $workflow, SubjectInterface $subject, OutputInterface $output): void
    {
        $recorded = new Steps();
        try {
            $steps = $generator->generate($workflow, $subject, $generatorOptions);
            $this->stepsRecorder->record($steps, $workflow, $subject, $recorded);
        } catch (Throwable $throwable) {
            $output->writeln([
                sprintf("<comment>There is an issue while trying workflow '%s':</comment>", $workflow->getName()),
                "<error>{$throwable->getMessage()}</error>",
            ]);
        } finally {
            if ($subject instanceof TearDownInterface) {
                $subject->tearDown();
            }
        }

        $this->renderTable($output, $recorded);
    }

    protected function renderTable(OutputInterface $output, Steps $recorded): void
    {
        $output->writeln([
            '<info>Trying workflow is finished! Here are steps:</info>',
        ]);

        $table = new Table($output);
        $table->setHeaders(['Transition', 'Data', 'Places']);
        foreach ($recorded as $step) {
            $table->addRow([$step->getTransition(), $step->getData()->serialize(), implode(', ', $step->getPlaces())]);
        }
        $table->render();
    }
}
