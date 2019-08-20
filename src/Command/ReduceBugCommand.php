<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\FinishReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;

class ReduceBugCommand extends AbstractCommand
{
    /**
     * @var ReducerManager
     */
    private $reducerManager;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var Registry
     */
    protected $workflowRegistry;

    public function __construct(
        ReducerManager $reducerManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        Registry $workflowRegistry
    ) {
        $this->reducerManager = $reducerManager;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->workflowRegistry = $workflowRegistry;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('mbt:bug:reduce')
            ->setDescription('Reduce the reproduce steps of the bug.')
            ->setHelp("Make bug's reproduce steps shorter.")
            ->addArgument('bug-id', InputArgument::REQUIRED, 'The bug id to reduce the steps.')
            ->addArgument('reducer', InputArgument::REQUIRED, 'The path reducer.')
            ->setHidden(true);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws Exception
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bugId = $input->getArgument('bug-id');
        $reducer = $input->getArgument('reducer');
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        $task = $bug->getTask();
        if (!$task instanceof Task) {
            throw new Exception(sprintf('Task of bug with id %d is missing', $bugId));
        }

        $workflow = WorkflowHelper::get($this->workflowRegistry, $task->getModel()->getName());
        if (WorkflowHelper::checksum($workflow) !== $bug->getModelHash()) {
            throw new Exception(sprintf('Model checksum of bug with id %d does not match', $bugId));
        }

        $reducer = $this->reducerManager->getReducer($reducer);
        $messagesCount = $reducer->dispatch($bug);
        if (0 === $messagesCount && 0 === $bug->getMessagesCount()) {
            $this->messageBus->dispatch(new FinishReduceBugMessage($bug->getId()));
        } elseif ($messagesCount > 0) {
            $callback = function () use ($bug, $messagesCount) {
                // Reload the bug for the newest messages count.
                $bug = $this->entityManager->find(Bug::class, $bug->getId(), LockMode::PESSIMISTIC_WRITE);

                if ($bug instanceof Bug) {
                    $bug->setMessagesCount($bug->getMessagesCount() + $messagesCount);
                }
            };

            $this->entityManager->transactional($callback);
        }
    }
}
