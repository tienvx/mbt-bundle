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
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;
use Tienvx\Bundle\MbtBundle\Service\BugProgressInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigLoaderInterface;

class ReduceStepsMessageHandler implements MessageHandlerInterface
{
    protected ReducerManager $reducerManager;
    protected EntityManagerInterface $entityManager;
    protected MessageBusInterface $messageBus;
    protected ConfigLoaderInterface $configLoader;
    protected BugProgressInterface $bugProgress;

    public function __construct(
        ReducerManager $reducerManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        ConfigLoaderInterface $configLoader,
        BugProgressInterface $bugProgress
    ) {
        $this->reducerManager = $reducerManager;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->configLoader = $configLoader;
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

        if ($bug->getModelVersion() !== $bug->getModel()->getVersion()) {
            // The model has been modified.
            return;
        }

        if (count($bug->getSteps()) !== $length) {
            // The bug has been reduced.
            return;
        }

        $reducer = $this->reducerManager->get($this->configLoader->getReducer());
        $reducer->handle($bug, $from, $to);

        $this->bugProgress->increaseProcessed($bug, 1);
        if ($bug->getProgress()->getProcessed() === $bug->getProgress()->getTotal()) {
            $this->messageBus->dispatch(new RecordVideoMessage($bug->getId()));
            $this->messageBus->dispatch(new ReportBugMessage($bug->getId()));
        }
    }
}
