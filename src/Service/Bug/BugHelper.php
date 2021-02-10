<?php

namespace Tienvx\Bundle\MbtBundle\Service\Bug;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManagerInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;

class BugHelper implements BugHelperInterface
{
    protected ReducerManagerInterface $reducerManager;
    protected EntityManagerInterface $entityManager;
    protected MessageBusInterface $messageBus;
    protected BugProgressInterface $bugProgress;
    protected BugNotifierInterface $notifyHelper;
    protected ProviderManager $providerManager;
    protected StepRunnerInterface $stepRunner;

    public function __construct(
        ReducerManagerInterface $reducerManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        BugProgressInterface $bugProgress,
        BugNotifierInterface $notifyHelper,
        ProviderManager $providerManager,
        StepRunnerInterface $stepRunner
    ) {
        $this->reducerManager = $reducerManager;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->bugProgress = $bugProgress;
        $this->notifyHelper = $notifyHelper;
        $this->providerManager = $providerManager;
        $this->stepRunner = $stepRunner;
    }

    public function reduceBug(int $bugId): void
    {
        $bug = $this->getBug($bugId, 'reduce bug');
        $this->startReducing($bug);

        $reducer = $this->reducerManager->getReducer($bug->getTask()->getTaskConfig()->getReducer());
        $messagesCount = $reducer->dispatch($bug);
        if (0 === $messagesCount && $bug->getProgress()->getProcessed() === $bug->getProgress()->getTotal()) {
            $this->stopReducing($bug);
            $this->messageBus->dispatch(new RecordVideoMessage($bug->getId()));
            $this->messageBus->dispatch(new ReportBugMessage($bug->getId()));
        } elseif ($messagesCount > 0) {
            $this->bugProgress->increaseTotal($bug, $messagesCount);
        }
    }

    public function reduceSteps(int $bugId, int $length, int $from, int $to): void
    {
        $bug = $this->getBug($bugId, 'reduce steps for bug');

        if (count($bug->getSteps()) !== $length) {
            // The bug has been reduced.
            return;
        }

        $reducer = $this->reducerManager->getReducer($bug->getTask()->getTaskConfig()->getReducer());
        $reducer->handle($bug, $from, $to);

        $this->bugProgress->increaseProcessed($bug, 1);
        if (!$bug->isReducing()) {
            $this->messageBus->dispatch(new RecordVideoMessage($bug->getId()));
            if ($bug->getTask()->getTaskConfig()->getNotifyChannels()) {
                $this->messageBus->dispatch(new ReportBugMessage($bug->getId()));
            }
        }
    }

    public function reportBug(int $bugId): void
    {
        $bug = $this->getBug($bugId, 'report bug');

        if ($bug->getTask()->getTaskConfig()->getNotifyChannels()) {
            $this->notifyHelper->notify($bug);
        }
    }

    public function recordVideo(int $bugId): void
    {
        $bug = $this->getBug($bugId, 'record video for bug');
        $driver = $this->providerManager->createDriver($bug->getTask(), $bug->getId());
        try {
            foreach ($bug->getSteps() as $step) {
                $this->stepRunner->run($step, $bug->getTask()->getModelRevision(), $driver);
            }
        } catch (ExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            // Do nothing.
        } finally {
            $driver->quit();
        }
    }

    public function createBug(array $steps, string $message): BugInterface
    {
        $bug = new Bug();
        $bug->setTitle('');
        $bug->setSteps($steps);
        $bug->setMessage($message);

        return $bug;
    }

    protected function getBug(int $bugId, string $action): BugInterface
    {
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug instanceof BugInterface) {
            throw new UnexpectedValueException(sprintf('Can not %s %d: bug not found', $action, $bugId));
        }

        return $bug;
    }

    protected function startReducing(BugInterface $bug): void
    {
        if ($bug->isReducing()) {
            throw new RuntimeException(sprintf('Bug %d is already reducing', $bug->getId()));
        } else {
            $bug->setReducing(true);
            $this->entityManager->flush();
        }
    }

    protected function stopReducing(BugInterface $bug): void
    {
        $bug->setReducing(false);
        // Reducing bug take long time. Reconnect to flush changes.
        $this->entityManager->getConnection()->connect();
        $this->entityManager->flush();
    }
}
