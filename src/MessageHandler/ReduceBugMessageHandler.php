<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\FinishReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;

class ReduceBugMessageHandler implements MessageHandlerInterface
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
    protected $workflowHelper;

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

    /**
     * @throws Exception
     */
    public function __invoke(ReduceBugMessage $message)
    {
        $bugId = $message->getId();
        $reducer = $message->getReducer();
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        $workflow = $this->workflowHelper->get($bug->getModel()->getName());
        if ($this->workflowHelper->checksum($workflow) !== $bug->getModelHash()) {
            throw new Exception(sprintf('Model checksum of bug with id %d does not match', $bugId));
        }

        $reducerService = $this->reducerManager->get($reducer);
        $messagesCount = $reducerService->dispatch($bug);
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
