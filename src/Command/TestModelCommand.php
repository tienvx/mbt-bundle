<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Registry;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Helper\StepsRunner;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class TestModelCommand extends Command
{
    use TokenTrait;
    use SubjectTrait;

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

    public function setWorkflowRegistry(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    protected function configure()
    {
        $this
            ->setName('mbt:model:test')
            ->setDescription('Test model and subject together.')
            ->setHelp('Call system under test to test model and print steps for it.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to test.')
            ->addOption('generator', 'g', InputOption::VALUE_OPTIONAL, 'The generator to generate steps from the model.', 'random')
            ->addOption('generator-options', 'o', InputOption::VALUE_OPTIONAL, 'The options for the generator (in json).', '{}');
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
        $subject = $this->getSubject($model, true);
        $workflow = WorkflowHelper::get($this->workflowRegistry, $model);
        $generator = $this->generatorManager->getGenerator($input->getOption('generator'));
        $generatorOptions = GeneratorOptions::deserialize($input->getOption('generator-options'));

        $this->setAnonymousToken();

        $recorded = new Steps();
        try {
            $steps = $generator->generate($workflow, $subject, $generatorOptions);
            StepsRunner::record($steps, $workflow, $subject, $recorded);
        } catch (Throwable $throwable) {
            $output->writeln([
                sprintf("<comment>There is an issue while testing model '%s':</comment>", $model),
                "<error>{$throwable->getMessage()}</error>",
            ]);
        } finally {
            $subject->tearDown();
        }

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
