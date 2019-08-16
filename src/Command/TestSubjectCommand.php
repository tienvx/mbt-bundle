<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Registry;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Entity\Step;
use Tienvx\Bundle\MbtBundle\Entity\StepData;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class TestSubjectCommand extends AbstractCommand
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
            ->setName('mbt:subject:test')
            ->setDescription('Call system under test to test model.')
            ->setHelp('Call system under test to test model.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to test.')
            ->addOption('generator', 'g', InputOption::VALUE_OPTIONAL, 'The generator to generate path from the model.', 'random')
            ->addOption('generator-options', 'o', InputOption::VALUE_OPTIONAL, 'The options for the generator.');
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
        $generator = $this->generatorManager->getGenerator($input->getOption('generator'));
        $generatorOptions = GeneratorOptions::denormalize($input->getOption('generator-options'));

        $this->setAnonymousToken();

        try {
            foreach ($generator->generate($workflow, $subject, $generatorOptions) as $step) {
                if ($step instanceof Step && $step->getTransition() && $step->getData() instanceof StepData) {
                    $workflow->apply($subject, $step->getTransition(), [
                        'data' => $step->getData(),
                    ]);
                }
            }
        } catch (Throwable $throwable) {
            $subjectClass = $this->subjectManager->getSubject($model);
            $output->writeln([
                sprintf("There is an issue while testing subject '%s':", $subjectClass),
                $throwable->getMessage(),
            ]);
        } finally {
            $subject->tearDown();
        }

        $output->writeln('Testing subject is finished!');
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
        $subject->setTestingSubject(true);
        $subject->setUp();

        return $subject;
    }
}
