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
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Message\CreateBugMessage;
use Tienvx\Bundle\MbtBundle\Message\UpdateTaskStatusMessage;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

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

    protected function configure()
    {
        $this
            ->setName('mbt:task:execute')
            ->setDescription('Execute a task.')
            ->setHelp('This command execute a task, then create a bug if found.')
            ->addArgument('task-id', InputArgument::REQUIRED, 'The task id to execute.');
    }

    public function setWorkflowRegistry(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
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
        if (!$this->workflowRegistry instanceof Registry) {
            throw new Exception('Can not execute task: No workflows were defined');
        }

        $taskId = $input->getArgument('task-id');

        $callback = function () use ($taskId) {
            $task = $this->entityManager->find(Task::class, $taskId);

            if ($task instanceof Task) {
                $task->setStatus('in-progress');
            }

            return $task;
        };

        $task = $this->entityManager->transactional($callback);

        if (!$task instanceof Task) {
            $output->writeln(sprintf('No task found for id %d', $taskId));

            return;
        }

        $this->setAnonymousToken();

        $subject = $this->subjectManager->createSubject($task->getModel());
        $subject->setUp();
        $generator = $this->generatorManager->getGenerator($task->getGenerator());
        $workflow = $this->workflowRegistry->get($subject, $task->getModel());

        $path = new Path();
        $path->add(null, null, $workflow->getDefinition()->getInitialPlaces());

        try {
            foreach ($generator->getAvailableTransitions($workflow, $subject, $task->getMetaData()) as $transitionName) {
                try {
                    if (!$generator->applyTransition($workflow, $subject, $transitionName)) {
                        throw new Exception(sprintf("Generator '%s' generated transition '%s' that can not be applied", $task->getGenerator(), $transitionName));
                    }
                } catch (Throwable $throwable) {
                    throw $throwable;
                } finally {
                    $data = $subject->getStoredData();
                    $places = array_keys(array_filter($workflow->getMarking($subject)->getPlaces()));
                    $path->add($transitionName, $data, $places);
                }
            }
        } catch (Throwable $throwable) {
            $message = new CreateBugMessage(
                $this->defaultBugTitle,
                Path::serialize($path),
                $path->countPlaces(),
                $throwable->getMessage(),
                $task->getId(),
                'new'
            );
            $this->messageBus->dispatch($message);
        } finally {
            $subject->tearDown();

            $this->messageBus->dispatch(new UpdateTaskStatusMessage($taskId, 'completed'));
        }
    }
}
