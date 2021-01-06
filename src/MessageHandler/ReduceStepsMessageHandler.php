<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManagerInterface;
use Tienvx\Bundle\MbtBundle\Service\BugProgressInterface;

class ReduceStepsMessageHandler implements MessageHandlerInterface
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
    public function __invoke(ReduceStepsMessage $message): void
    {
        $bugId = $message->getBugId();
        $length = $message->getLength();
        $from = $message->getFrom();
        $to = $message->getTo();
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug instanceof BugInterface) {
            throw new UnexpectedValueException(sprintf('Can not reduce steps for bug %d: bug not found', $bugId));
        }

        if ($bug->getModelVersion() !== $bug->getTask()->getModel()->getVersion()) {
            // The model has been modified.
            return;
        }

        if (count($bug->getSteps()) !== $length) {
            // The bug has been reduced.
            return;
        }

        $reducer = $this->reducerManager->getReducer($bug->getTask()->getTaskConfig()->getReducer());
        $reducer->handle($bug, $from, $to);

        $this->bugProgress->increaseProcessed($bug, 1);
        if ($bug->getProgress()->getProcessed() === $bug->getProgress()->getTotal()) {
            $this->messageBus->dispatch(new RecordVideoMessage($bug->getId()));
            $this->messageBus->dispatch(new ReportBugMessage($bug->getId()));
        }
    }
}
