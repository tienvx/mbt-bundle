<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Step;
use Tienvx\Bundle\MbtBundle\Entity\StepData;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Entity\Path;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\CreateBugMessage;
use Tienvx\Bundle\MbtBundle\Message\ApplyTaskTransitionMessage;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;
use Tienvx\Bundle\MbtBundle\Workflow\TaskWorkflow;

class ExecuteTaskCommand extends AbstractCommand
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

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var string
     */
    private $defaultBugTitle;

    public function __construct(
        Registry $workflowRegistry,
        SubjectManager $subjectManager,
        GeneratorManager $generatorManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->subjectManager = $subjectManager;
        $this->generatorManager = $generatorManager;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;

        parent::__construct();
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

        $path = new Path();
        $path->addStep(new Step(null, new StepData(), $workflow->getDefinition()->getInitialPlaces()));

        try {
            foreach ($generator->generate($workflow, $subject, $task->getGeneratorOptions()) as $step) {
                if ($step instanceof Step && $step->getTransition() && $step->getData() instanceof StepData) {
                    try {
                        $workflow->apply($subject, $step->getTransition(), [
                            'data' => $step->getData(),
                        ]);
                    } catch (Throwable $throwable) {
                        throw $throwable;
                    } finally {
                        $places = array_keys(array_filter($workflow->getMarking($subject)->getPlaces()));
                        $step->setPlaces($places);
                        $path->addStep($step);
                    }
                }
            }
        } catch (Throwable $throwable) {
            $this->createBug($path, $throwable->getMessage(), $taskId);
        } finally {
            $subject->tearDown();

            $this->messageBus->dispatch(new ApplyTaskTransitionMessage($taskId, TaskWorkflow::COMPLETE));
        }
    }

    private function createBug(Path $path, string $bugMessage, int $taskId)
    {
        $message = new CreateBugMessage(
            $this->defaultBugTitle,
            $path->serialize(),
            $path->countPlaces(),
            $bugMessage,
            $taskId,
            'new'
        );
        $this->messageBus->dispatch($message);
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
        $subject->setUp();

        return $subject;
    }
}
