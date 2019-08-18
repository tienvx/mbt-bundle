<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
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
     * @var EntityManagerInterface
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
            ->addArgument('reducer', InputArgument::REQUIRED, 'The path reducer.');
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
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug instanceof Bug) {
            $output->writeln(sprintf('No bug found for id %d', $bugId));

            return;
        }

        $workflow = WorkflowHelper::get($this->workflowRegistry, $bug->getTask()->getModel()->getName());
        if (WorkflowHelper::checksum($workflow) !== $bug->getModelHash()) {
            return;
        }

        $reducer = $this->reducerManager->getReducer($reducer);
        $messagesCount = $reducer->dispatch($bug);
        if (0 === $messagesCount && 0 === $bug->getMessagesCount()) {
            $this->messageBus->dispatch(new FinishReduceBugMessage($bug->getId()));
        } else {
            $callback = function () use ($bug, $messagesCount) {
                $this->entityManager->lock($bug, LockMode::PESSIMISTIC_WRITE);

                $bug->setMessagesCount($bug->getMessagesCount() + $messagesCount);
            };

            $this->entityManager->transactional($callback);
        }
    }
}
