<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\FinishReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;

class ReduceStepsMessageHandler implements MessageHandlerInterface
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
     * @var WorkflowHelper
     */
    private $workflowHelper;

    public function __construct(
        ReducerManager $reducerManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        WorkflowHelper $workflowHelper
    ) {
        $this->reducerManager = $reducerManager;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->workflowHelper = $workflowHelper;
    }

    public function __invoke(ReduceStepsMessage $message): void
    {
        $bugId = $message->getBugId();
        $reducer = $message->getReducer();
        $length = $message->getLength();
        $from = $message->getFrom();
        $to = $message->getTo();
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug || !$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        if ($this->workflowHelper->checksum($bug->getWorkflow()->getName()) !== $bug->getWorkflowHash()) {
            throw new Exception(sprintf('Model checksum of bug with id %d does not match', $bugId));
        }

        $this->reduce($reducer, $bug, $length, $from, $to);
    }

    protected function reduce(string $reducer, Bug $bug, int $length, int $from, int $to): void
    {
        $reducerService = $this->reducerManager->get($reducer);
        $reducerService->handle($bug, $length, $from, $to);

        $this->messageBus->dispatch(new FinishReduceStepsMessage($bug->getId()));
    }
}
