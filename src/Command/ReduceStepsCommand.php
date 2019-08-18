<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;

class ReduceStepsCommand extends AbstractCommand
{
    /**
     * @var ReducerManager
     */
    private $reducerManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Registry
     */
    protected $workflowRegistry;

    public function __construct(
        ReducerManager $reducerManager,
        EntityManagerInterface $entityManager,
        Registry $workflowRegistry
    ) {
        $this->reducerManager = $reducerManager;
        $this->entityManager = $entityManager;
        $this->workflowRegistry = $workflowRegistry;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:steps:reduce')
            ->setDescription("Handle a path reducer's message.")
            ->setHelp('Call path reducer to handle a message that was come from itself')
            ->addArgument('bug-id', InputArgument::REQUIRED, 'The bug id.')
            ->addArgument('reducer', InputArgument::REQUIRED, 'The path reducer.')
            ->addArgument('length', InputArgument::REQUIRED, 'The path length.')
            ->addArgument('from', InputArgument::REQUIRED, 'From places.')
            ->addArgument('to', InputArgument::REQUIRED, 'To places.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bugId = $input->getArgument('bug-id');
        $reducer = $input->getArgument('reducer');
        $length = $input->getArgument('length');
        $from = $input->getArgument('from');
        $to = $input->getArgument('to');
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug || !$bug instanceof Bug) {
            $output->writeln(sprintf('No bug found for id %d', $bugId));

            return;
        }

        $task = $bug->getTask();
        if (!$task instanceof Task) {
            $output->writeln(sprintf('Task of bug with id %d is missing', $bugId));

            return;
        }

        $workflow = WorkflowHelper::get($this->workflowRegistry, $task->getModel()->getName());
        if (WorkflowHelper::checksum($workflow) !== $bug->getModelHash()) {
            return;
        }

        $this->setAnonymousToken();

        $reducer = $this->reducerManager->getReducer($reducer);
        $reducer->handle($bug, $workflow, $length, $from, $to);
    }
}
