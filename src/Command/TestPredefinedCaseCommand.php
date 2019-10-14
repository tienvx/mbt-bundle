<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Helper\StepsRunner;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\PredefinedCase\PredefinedCaseManager;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class TestPredefinedCaseCommand extends Command
{
    use TokenTrait;
    use SubjectTrait;
    use MessageTrait;

    /**
     * @var Registry
     */
    private $workflowRegistry;

    /**
     * @var GeneratorManager
     */
    private $generatorManager;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var string
     */
    private $defaultBugTitle;

    /**
     * @var PredefinedCaseManager
     */
    private $predefinedCaseManager;

    public function __construct(
        SubjectManager $subjectManager,
        GeneratorManager $generatorManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        PredefinedCaseManager $predefinedCaseManager
    ) {
        $this->subjectManager = $subjectManager;
        $this->generatorManager = $generatorManager;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->predefinedCaseManager = $predefinedCaseManager;

        parent::__construct();
    }

    public function setWorkflowRegistry(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    protected function configure()
    {
        $this
            ->setName('mbt:predefined-case:test')
            ->setDescription('Test a predefined case to see if it has a bug or not.')
            ->setHelp('Test a predefined case, create a new bug if needed.')
            ->addArgument('name', InputArgument::REQUIRED, 'The predefined case name.');
    }

    public function setDefaultBugTitle(string $defaultBugTitle)
    {
        $this->defaultBugTitle = $defaultBugTitle;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        if (!$this->predefinedCaseManager->has($name)) {
            throw new Exception(sprintf('No pre-defined case found for name %s', $name));
        }

        $predefinedCase = $this->predefinedCaseManager->get($name);
        $model = $predefinedCase->getModel()->getName();
        $workflow = WorkflowHelper::get($this->workflowRegistry, $model);
        $subject = $this->getSubject($model);

        $this->setAnonymousToken();

        $recorded = new Steps();
        try {
            StepsRunner::record($predefinedCase->getSteps(), $workflow, $subject, $recorded);
        } catch (Throwable $throwable) {
            $this->createBug($this->defaultBugTitle, $recorded, $throwable->getMessage(), null, $model);
        } finally {
            $subject->tearDown();
        }

        $output->writeln('Testing predefined case is finished!');
    }
}
