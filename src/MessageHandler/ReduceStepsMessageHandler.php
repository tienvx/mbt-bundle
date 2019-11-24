<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\TokenHelper;
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
     * @var TokenHelper
     */
    private $tokenHelper;

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
        TokenHelper $tokenHelper,
        MessageBusInterface $messageBus,
        WorkflowHelper $workflowHelper
    ) {
        $this->reducerManager = $reducerManager;
        $this->entityManager = $entityManager;
        $this->tokenHelper = $tokenHelper;
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

        $workflow = $this->workflowHelper->get($bug->getModel()->getName());
        if ($this->workflowHelper->checksum($workflow) !== $bug->getModelHash()) {
            throw new Exception(sprintf('Model checksum of bug with id %d does not match', $bugId));
        }

        $this->tokenHelper->setAnonymousToken();

        $reducerService = $this->reducerManager->get($reducer);
        $reducerService->handle($bug, $workflow, $length, $from, $to);

        $this->messageBus->dispatch(new FinishReduceStepsMessage($bug->getId()));
    }
}
