<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Task;

class UpdateTaskStatusCommand extends AbstractCommand
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:task:update-status')
            ->setDescription('Update status of a task.')
            ->setHelp('This command update status of a task.')
            ->addArgument('task-id', InputArgument::REQUIRED, 'The task id to update.')
            ->addArgument('status', InputArgument::REQUIRED, 'The status to update.');
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
            $output->writeln(sprintf('No task found for id %d', $taskId));

            return;
        }

        $status = $input->getArgument('status');
        $task->setStatus($status);

        $errors = $this->validator->validate($task);

        if (count($errors) > 0) {
            $output->writeln((string) $errors);

            return;
        }

        $this->entityManager->flush();
    }
}
