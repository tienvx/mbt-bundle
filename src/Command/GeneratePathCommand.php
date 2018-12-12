<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Helper\Constants;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class GeneratePathCommand extends AbstractCommand
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
        $this->subjectManager   = $subjectManager;
        $this->generatorManager = $generatorManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:path:generate')
            ->setDescription('Generate path from model.')
            ->setHelp('Generate path from model.')
            ->addArgument('model', InputArgument::REQUIRED, 'The model to generate.')
            ->addOption('generator', 'g', InputOption::VALUE_OPTIONAL, 'The generator to generate path from the model.', Constants::DEFAULT_GENERATOR);
    }

    public function setWorkflowRegistry(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->workflowRegistry instanceof Registry) {
            throw new Exception('Can not generate path: No workflows were defined');
        }

        $this->setAnonymousToken();

        $model = $input->getArgument('model');
        $generatorName = $input->getOption('generator');
        $generator = $this->generatorManager->getGenerator($generatorName);
        $subject = $this->subjectManager->createSubject($model);
        $subject->setTesting(true);
        $subject->setUp();
        $workflow = $this->workflowRegistry->get($subject, $model);

        $path = new Path();
        $path->add(null, null, [$workflow->getDefinition()->getInitialPlace()]);

        try {
            foreach ($generator->getAvailableTransitions($workflow, $subject) as $transitionName) {
                try {
                    if (!$generator->applyTransition($workflow, $subject, $transitionName)) {
                        throw new Exception(sprintf('Generator %s generated transition %s that can not be applied', $generatorName, $transitionName));
                    }
                } finally {
                    $data = $subject->getStoredData();
                    $places = array_keys(array_filter($workflow->getMarking($subject)->getPlaces()));
                    $path->add($transitionName, $data, $places);
                }
            }
        } finally {
            $subject->tearDown();
        }

        $output->writeln(Path::serialize($path));
    }
}
