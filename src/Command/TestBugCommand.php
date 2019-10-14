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
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Helper\BugHelper;
use Tienvx\Bundle\MbtBundle\Helper\StepsRunner;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;

class TestBugCommand extends Command
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

    public function __construct(
        SubjectManager $subjectManager,
        GeneratorManager $generatorManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus
    ) {
        $this->subjectManager = $subjectManager;
        $this->generatorManager = $generatorManager;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;

        parent::__construct();
    }

    public function setWorkflowRegistry(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    protected function configure()
    {
        $this
            ->setName('mbt:bug:test')
            ->setDescription('Test a bug to see if it is still replicable or not.')
            ->setHelp('Test a bug, update the bug, or create a new bug if needed.')
            ->addArgument('bug-id', InputArgument::REQUIRED, 'The bug to test.');
    }

    public function setDefaultBugTitle(string $defaultBugTitle)
    {
        $this->defaultBugTitle = $defaultBugTitle;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bugId = $input->getArgument('bug-id');
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        if (BugWorkflow::CLOSED !== $bug->getStatus()) {
            throw new Exception(sprintf('Can not test bug with id %d, only closed bug can be tested again', $bugId));
        }

        $workflow = WorkflowHelper::get($this->workflowRegistry, $bug->getModel()->getName());
        if (WorkflowHelper::checksum($workflow) !== $bug->getModelHash()) {
            throw new Exception(sprintf('Model checksum of bug with id %d does not match', $bugId));
        }

        $subject = $this->getSubject($bug->getModel()->getName());
        $this->setAnonymousToken();

        $recorded = new Steps();
        try {
            StepsRunner::record($bug->getSteps(), $workflow, $subject, $recorded);
        } catch (Throwable $throwable) {
            if ($throwable->getMessage() === $bug->getBugMessage()) {
                if ($recorded->getLength() < $bug->getSteps()->getLength()) {
                    BugHelper::updateSteps($this->entityManager, $bug, $recorded);
                }
                $this->applyBugTransition($bugId, BugWorkflow::REOPEN);
            } else {
                $this->createBug($this->defaultBugTitle, $recorded, $throwable->getMessage(), null, $bug->getModel()->getName());
            }
        } finally {
            $subject->tearDown();
        }

        $output->writeln('Testing bug is finished!');
    }
}
