<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Command\MessageTrait;
use Tienvx\Bundle\MbtBundle\Command\WorkflowRegisterTrait;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;

class ReduceBugMessageHandler implements MessageHandlerInterface
{
    use MessageTrait;
    use WorkflowRegisterTrait;

    /**
     * @var ReducerManager
     */
    private $reducerManager;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        ReducerManager $reducerManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus
    ) {
        $this->reducerManager = $reducerManager;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
    }

    /**
     * @param ReduceBugMessage $message
     *
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

        $workflow = WorkflowHelper::get($this->workflowRegistry, $bug->getModel()->getName());
        if (WorkflowHelper::checksum($workflow) !== $bug->getModelHash()) {
            throw new Exception(sprintf('Model checksum of bug with id %d does not match', $bugId));
        }

        $reducerService = $this->reducerManager->getReducer($reducer);
        $messagesCount = $reducerService->dispatch($bug);
        if (0 === $messagesCount && 0 === $bug->getMessagesCount()) {
            $this->finishReduceBug($bug->getId());
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
