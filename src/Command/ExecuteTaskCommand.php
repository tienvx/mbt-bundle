<?php

namespace Tienvx\Bundle\MbtBundle\Command;

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
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Helper\StepsRunner;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;
use Tienvx\Bundle\MbtBundle\Workflow\TaskWorkflow;

class ExecuteTaskCommand extends Command
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
     * @var EntityManagerInterface
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
            ->setName('mbt:task:execute')
            ->setDescription('Execute a task.')
            ->setHelp('This command execute a task, then create a bug if found.')
            ->addArgument('task-id', InputArgument::REQUIRED, 'The task id to execute.');
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
        $taskId = $input->getArgument('task-id');
        $task = $this->entityManager->find(Task::class, $taskId);

        if (!$task instanceof Task) {
            $output->writeln(sprintf('No task found for id %d', $taskId));

            return;
        }

        $subject = $this->getSubject($task->getModel()->getName());
        $generator = $this->generatorManager->getGenerator($task->getGenerator()->getName());
        $workflow = WorkflowHelper::get($this->workflowRegistry, $task->getModel()->getName());

        $this->setAnonymousToken();

        $recorded = new Steps();
        try {
            $steps = $generator->generate($workflow, $subject, $task->getGeneratorOptions());
            StepsRunner::record($steps, $workflow, $subject, $recorded);
        } catch (Throwable $throwable) {
            $this->createBug($this->defaultBugTitle, $recorded, $throwable->getMessage(), $taskId, $task->getModel()->getName());
        } finally {
            $subject->tearDown();

            $this->applyTaskTransition($taskId, TaskWorkflow::COMPLETE);
        }
    }
}
