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
use Tienvx\Bundle\MbtBundle\Generator\GeneratorArgumentsTrait;
use Tienvx\Bundle\MbtBundle\Service\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
use Tienvx\Bundle\MbtBundle\Service\PathReducerManager;
use Tienvx\Bundle\MbtBundle\Service\ReporterManager;
use Tienvx\Bundle\MbtBundle\Service\StopConditionManager;

class ExecuteTaskCommand extends Command
{
    use GeneratorArgumentsTrait;

    private $modelRegistry;
    private $generatorManager;
    private $pathReducerManager;
    private $entityManager;
    private $reporterManager;
    private $stopConditionManager;
    private $defaultReporter;

    public function __construct(
        ModelRegistry $modelRegistry,
        GeneratorManager $generatorManager,
        PathReducerManager $pathReducerManager,
        EntityManagerInterface $entityManager,
        ReporterManager $reporterManager,
        StopConditionManager $stopConditionManager)
    {
        $this->modelRegistry = $modelRegistry;
        $this->generatorManager = $generatorManager;
        $this->pathReducerManager = $pathReducerManager;
        $this->entityManager = $entityManager;
        $this->reporterManager = $reporterManager;
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

    public function setDefaultReporter(string $defaultReporter)
    {
        $this->defaultReporter = $defaultReporter;
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
        $stopCondition = $this->stopConditionManager->getStopCondition($task->getStopCondition());
        $stopCondition->setArguments(json_decode($task->getStopConditionArguments(), true));

        $generator->init($model, $stopCondition);

        try {
            while (!$generator->meetStopCondition() && $edge = $generator->getNextStep()) {
                $generator->goToNextStep($edge);
            }
        }
        catch (Throwable $throwable) {
            $path = $generator->getPath();
            $reducer = $task->getReducer();
            if ($reducer) {
                $pathReducer = $this->pathReducerManager->getPathReducer($reducer);
                $path = $pathReducer->reduce($path, $model, $throwable);
            }

            if ($this->reporterManager->hasReporter($this->defaultReporter)) {
                $bug = new Bug();
                $bug->setTitle($throwable->getMessage());
                $bug->setMessage($throwable->getMessage());
                $bug->setTask($task);
                $bug->setSteps($path);
                $bug->setStatus('unverified');
                $bug->setReporter($this->defaultReporter);
                $this->entityManager->persist($bug);
                $this->entityManager->flush();
            }
        }
    }
}
