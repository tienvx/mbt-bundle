<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Registry;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Graph\Path;
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
     * @var string
     */
    private $defaultBugTitle;

    public function __construct(
        Registry $workflowRegistry,
        SubjectManager $subjectManager,
        GeneratorManager $generatorManager,
        EntityManagerInterface $entityManager)
    {
        $this->workflowRegistry     = $workflowRegistry;
        $this->subjectManager       = $subjectManager;
        $this->generatorManager     = $generatorManager;
        $this->entityManager        = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:execute-task')
            ->setDescription('Execute a task.')
            ->setHelp('This command execute a task, then create a bug if found.')
            ->addArgument('task-id', InputArgument::REQUIRED, 'The task id to execute.');
    }

    public function setDefaultBugTitle(string $defaultBugTitle)
    {
        $this->defaultBugTitle = $defaultBugTitle;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $taskId = $input->getArgument('task-id');
        $task = $this->entityManager->getRepository(Task::class)->find($taskId);

        if (!$task || !$task instanceof Task) {
            $output->writeln(sprintf('No task found for id %d', $taskId));
            return;
        }

        $this->setAnonymousToken();

        $subject = $this->subjectManager->createSubjectForModel($task->getModel());
        $subject->setUp();
        $generator = $this->generatorManager->getGenerator($task->getGenerator());
        $workflow = $this->workflowRegistry->get($subject, $task->getModel());

        $path = new Path();
        $path->add(null, null, [$workflow->getDefinition()->getInitialPlace()]);

        try {
            foreach ($generator->getAvailableTransitions($workflow, $subject) as $transitionName) {
                $data = $subject->getData();
                $places = array_keys(array_filter($workflow->getMarking($subject)->getPlaces()));
                $path->add($transitionName, $data, $places);
                if (!$generator->applyTransition($workflow, $subject, $transitionName)) {
                    throw new Exception(sprintf('Generator %s generated transition %s that can not be applied', $task->getGenerator(), $transitionName));
                }
            }
        } catch (Throwable $throwable) {
            $bug = new Bug();
            $bug->setTitle($this->defaultBugTitle);
            $bug->setPath($path);
            $bug->setLength($path->countPlaces());
            $bug->setBugMessage($throwable->getMessage());
            $bug->setTask($task);
            $bug->setStatus('unverified');
            $this->entityManager->persist($bug);
            $this->entityManager->flush();
        } finally {
            $subject->tearDown();
        }
    }
}
