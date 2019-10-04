<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\FinishReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;

class ReduceStepsCommand extends Command
{
    use TokenTrait;

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

    /**
     * @var MessageBusInterface
     */
    protected $messageBus;

    public function __construct(
        ReducerManager $reducerManager,
        EntityManagerInterface $entityManager,
        Registry $workflowRegistry,
        MessageBusInterface $messageBus
    ) {
        $this->reducerManager = $reducerManager;
        $this->entityManager = $entityManager;
        $this->workflowRegistry = $workflowRegistry;
        $this->messageBus = $messageBus;

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
            ->addArgument('to', InputArgument::REQUIRED, 'To places.')
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
        $bugId = $input->getArgument('bug-id');
        $reducer = $input->getArgument('reducer');
        $length = $input->getArgument('length');
        $from = $input->getArgument('from');
        $to = $input->getArgument('to');
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug || !$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        $workflow = WorkflowHelper::get($this->workflowRegistry, $bug->getModel()->getName());
        if (WorkflowHelper::checksum($workflow) !== $bug->getModelHash()) {
            throw new Exception(sprintf('Model checksum of bug with id %d does not match', $bugId));
        }

        $this->setAnonymousToken();

        $reducerService = $this->reducerManager->getReducer($reducer);
        $reducerService->handle($bug, $workflow, $length, $from, $to);

        $this->finish($bug);
    }

    public function finish(Bug $bug): void
    {
        $this->messageBus->dispatch(new FinishReduceStepsMessage($bug->getId()));
    }
}
