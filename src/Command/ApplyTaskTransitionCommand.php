<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Workflow\TaskWorkflow;

class ApplyTaskTransitionCommand extends Command
{
    /**
     * @var TaskWorkflow
     */
    private $taskWorkflow;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, TaskWorkflow $taskWorkflow)
    {
        $this->entityManager = $entityManager;
        $this->taskWorkflow = $taskWorkflow;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:task:apply-transition')
            ->setDescription('Apply transition of a task.')
            ->setHelp("This command update status of a task by applying a transition of task's workflow.")
            ->addArgument('task-id', InputArgument::REQUIRED, 'The task id to update.')
            ->addArgument('transition', InputArgument::REQUIRED, 'The transition to apply.')
            ->setHidden(true);
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
        $task = $this->entityManager->getRepository(Task::class)->find($taskId);

        if (!$task || !$task instanceof Task) {
            throw new Exception(sprintf('No task found for id %d', $taskId));
        }

        $transition = $input->getArgument('transition');

        $this->taskWorkflow->apply($task, $transition);
        $this->entityManager->flush();
    }
}
