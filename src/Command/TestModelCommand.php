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
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Helper\ModelHelper;
use Tienvx\Bundle\MbtBundle\Helper\Steps\Recorder as StepsRecorder;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class TestModelCommand extends Command
{
    protected static $defaultName = 'mbt:model:test';

    /**
     * @var SubjectManager
     */
    private $subjectManager;

    /**
     * @var GeneratorManager
     */
    private $generatorManager;

    /**
     * @var ModelHelper
     */
    private $modelHelper;

    /**
     * @var StepsRecorder
     */
    private $stepsRecorder;

    public function __construct(
        SubjectManager $subjectManager,
        GeneratorManager $generatorManager,
        ModelHelper $modelHelper,
        StepsRecorder $stepsRecorder
    ) {
        $this->subjectManager = $subjectManager;
        $this->generatorManager = $generatorManager;
        $this->modelHelper = $modelHelper;
        $this->stepsRecorder = $stepsRecorder;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Test model and subject together.')
            ->setHelp('Call system under test to test model and print steps for it.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to test.')
            ->addOption('generator', 'g', InputOption::VALUE_OPTIONAL, 'The generator to generate steps from the model.', 'random')
            ->addOption('generator-options', 'o', InputOption::VALUE_OPTIONAL, 'The options for the generator (in json).', '{}');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $modelName = $input->getArgument('model');
        $subject = $this->subjectManager->createAndSetUp($modelName, true);
        $model = $this->modelHelper->get($modelName);
        $generator = $this->generatorManager->get($input->getOption('generator'));
        $generatorOptions = GeneratorOptions::deserialize($input->getOption('generator-options'));

        $this->test($generator, $generatorOptions, $model, $subject, $output);

        return 0;
    }

    protected function test(GeneratorInterface $generator, GeneratorOptions $generatorOptions, Model $model, SubjectInterface $subject, OutputInterface $output): void
    {
        $recorded = new Steps();
        try {
            $steps = $generator->generate($model, $subject, $generatorOptions);
            $this->stepsRecorder->record($steps, $model, $subject, $recorded);
        } catch (Throwable $throwable) {
            $output->writeln([
                sprintf("<comment>There is an issue while testing model '%s':</comment>", $model->getName()),
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
