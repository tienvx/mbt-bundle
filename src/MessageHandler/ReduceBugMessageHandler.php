<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManagerInterface;
use Tienvx\Bundle\MbtBundle\Service\BugProgressInterface;

class ReduceBugMessageHandler implements MessageHandlerInterface
{
    protected ReducerManagerInterface $reducerManager;
    protected EntityManagerInterface $entityManager;
    protected MessageBusInterface $messageBus;
    protected BugProgressInterface $bugProgress;

    public function __construct(
        ReducerManagerInterface $reducerManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        BugProgressInterface $bugProgress
    ) {
        $this->reducerManager = $reducerManager;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->bugProgress = $bugProgress;
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(ReduceBugMessage $message): void
    {
        $bugId = $message->getId();
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug instanceof BugInterface) {
            throw new UnexpectedValueException(sprintf('Can not reduce bug %d: bug not found', $bugId));
        }

        $reducer = $this->reducerManager->getReducer($bug->getTask()->getTaskConfig()->getReducer());
        $messagesCount = $reducer->dispatch($bug);
        if (0 === $messagesCount && $bug->getProgress()->getProcessed() === $bug->getProgress()->getTotal()) {
            $this->messageBus->dispatch(new RecordVideoMessage($bug->getId()));
            $this->messageBus->dispatch(new ReportBugMessage($bug->getId()));
        } elseif ($messagesCount > 0) {
            $this->bugProgress->increaseTotal($bug, $messagesCount);
        }
    }
}
