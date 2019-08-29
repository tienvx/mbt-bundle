<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;

class CreateBugCommand extends Command
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Registry
     */
    protected $workflowRegistry;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        Registry $workflowRegistry
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->workflowRegistry = $workflowRegistry;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:bug:create')
            ->setDescription('Create a bug.')
            ->setHelp('Create a bug.')
            ->addArgument('title', InputArgument::REQUIRED, 'Bug title.')
            ->addArgument('steps', InputArgument::REQUIRED, 'Bug steps.')
            ->addArgument('message', InputArgument::REQUIRED, 'Bug message.')
            ->addArgument('task-id', InputArgument::REQUIRED, 'Task id.')
            ->addArgument('status', InputArgument::REQUIRED, 'Bug status.')
            ->addArgument('model', InputArgument::REQUIRED, 'Model name.')
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
        $title = $input->getArgument('title');
        $steps = $input->getArgument('steps');
        $message = $input->getArgument('message');
        $taskId = $input->getArgument('task-id');
        $status = $input->getArgument('status');
        $model = $input->getArgument('model');

        $workflow = WorkflowHelper::get($this->workflowRegistry, $model);

        $bug = new Bug();
        $bug->setTitle($title);
        $bug->setSteps(Steps::deserialize($steps));
        $bug->setModel(new Model($model));
        $bug->setModelHash(WorkflowHelper::checksum($workflow));
        $bug->setBugMessage($message);
        $bug->setStatus($status);

        if ($taskId) {
            $task = $this->entityManager->getRepository(Task::class)->find($taskId);
            if ($task instanceof Task) {
                $bug->setTask($task);
            }
        }

        $errors = $this->validator->validate($bug);

        if (count($errors) > 0) {
            throw new Exception(sprintf('Invalid bug. Reason: %s', (string) $errors));
        }

        $this->entityManager->persist($bug);
        $this->entityManager->flush();
    }
}
