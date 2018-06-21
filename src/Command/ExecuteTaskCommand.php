<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Service\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\StopConditionManager;

class ExecuteTaskCommand extends Command
{
    private $modelRegistry;
    private $generatorManager;
    private $entityManager;
    private $stopConditionManager;
    private $defaultBugTitle;

    public function __construct(
        ModelRegistry $modelRegistry,
        GeneratorManager $generatorManager,
        EntityManagerInterface $entityManager,
        StopConditionManager $stopConditionManager)
    {
        $this->modelRegistry = $modelRegistry;
        $this->generatorManager = $generatorManager;
        $this->entityManager = $entityManager;
        $this->stopConditionManager = $stopConditionManager;

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
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $taskId = $input->getArgument('task-id');
        $task = $this->entityManager->getRepository(Task::class)->find($taskId);

        if (!$task || !$task instanceof Task) {
            $output->writeln(sprintf('No task found for id %d', $taskId));
            return;
        }

        $generator = $this->generatorManager->getGenerator($task->getGenerator());
        $model = $this->modelRegistry->getModel($task->getModel());
        $subject = $model->createSubject();
        $subject->setUp();
        $stopCondition = $this->stopConditionManager->getStopCondition($task->getStopCondition());
        $stopCondition->setArguments(json_decode($task->getStopConditionArguments(), true));

        $generator->init($model, $subject, $stopCondition);

        try {
            while (!$generator->meetStopCondition() && $edge = $generator->getNextStep()) {
                $generator->goToNextStep($edge);
            }
        } catch (Throwable $throwable) {
            $path = $generator->getPath();
            $bug = new Bug();
            $bug->setTitle($this->defaultBugTitle);
            $bug->setSteps($path);
            $bug->setLength($path->countEdges());
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
