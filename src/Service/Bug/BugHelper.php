<?php

namespace Tienvx\Bundle\MbtBundle\Service\Bug;

use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManagerInterface;
use Tienvx\Bundle\MbtBundle\Repository\BugRepositoryInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\RecordStepsRunner;
use Tienvx\Bundle\MbtBundle\Service\Step\StepHelperInterface;

class BugHelper implements BugHelperInterface
{
    protected ReducerManagerInterface $reducerManager;
    protected BugRepositoryInterface $bugRepository;
    protected MessageBusInterface $messageBus;
    protected BugNotifierInterface $bugNotifier;
    protected StepHelperInterface $stepHelper;
    protected RecordStepsRunner $stepsRunner;
    protected ConfigInterface $config;

    public function __construct(
        ReducerManagerInterface $reducerManager,
        BugRepositoryInterface $bugRepository,
        MessageBusInterface $messageBus,
        BugNotifierInterface $bugNotifier,
        StepHelperInterface $stepHelper,
        RecordStepsRunner $stepsRunner,
        ConfigInterface $config
    ) {
        $this->reducerManager = $reducerManager;
        $this->bugRepository = $bugRepository;
        $this->messageBus = $messageBus;
        $this->bugNotifier = $bugNotifier;
        $this->stepHelper = $stepHelper;
        $this->stepsRunner = $stepsRunner;
        $this->config = $config;
    }

    public function reduceBug(int $bugId): void
    {
        $bug = $this->getBug($bugId, 'reduce bug');

        $reducer = $this->reducerManager->getReducer($this->config->getReducer());
        $messagesCount = $reducer->dispatch($bug);
        if ($messagesCount > 0) {
            $this->bugRepository->increaseTotal($bug, $messagesCount);
        } elseif ($bug->getProgress()->getProcessed() === $bug->getProgress()->getTotal()) {
            $this->recordAndReport($bug);
        }
    }

    public function reduceSteps(int $bugId, int $length, int $from, int $to): void
    {
        $bug = $this->getBug($bugId, 'reduce steps for bug');

        if (count($bug->getSteps()) !== $length) {
            // The bug has been reduced.
            return;
        }

        $reducer = $this->reducerManager->getReducer($this->config->getReducer());
        $reducer->handle($bug, $from, $to);

        $this->bugRepository->increaseProcessed($bug);
        if (
            $bug->getProgress()->getTotal() > 0
            && $bug->getProgress()->getProcessed() === $bug->getProgress()->getTotal()
        ) {
            $this->recordAndReport($bug);
        }
    }

    public function reportBug(int $bugId): void
    {
        $bug = $this->getBug($bugId, 'report bug');
        $this->bugNotifier->notify($bug);
    }

    /**
     * @throws ExceptionInterface
     */
    public function recordVideo(int $bugId): void
    {
        $bug = $this->getBug($bugId, 'record video for bug');

        if ($bug->getVideo()->isRecording()) {
            throw new RecoverableMessageHandlingException(
                sprintf('Can not record video for bug %d: bug is recording. Will retry later', $bug->getId())
            );
        }

        try {
            $this->bugRepository->startRecording($bug);
            $bug->setDebug(true);
            $this->stepsRunner->run(
                $this->stepHelper->cloneAndResetSteps($bug->getSteps(), $bug->getTask()->getModelRevision()),
                $bug,
                function (Throwable $throwable) use ($bug) {
                    $bug->getVideo()->setErrorMessage(
                        $throwable->getMessage() !== $bug->getMessage() ? $throwable->getMessage() : null
                    );
                }
            );
        } finally {
            $this->bugRepository->stopRecording($bug);
        }
    }

    protected function getBug(int $bugId, string $action): BugInterface
    {
        $bug = $this->bugRepository->find($bugId);

        if (!$bug instanceof BugInterface) {
            throw new UnexpectedValueException(sprintf('Can not %s %d: bug not found', $action, $bugId));
        }

        return $bug;
    }

    protected function recordAndReport(BugInterface $bug): void
    {
        $this->messageBus->dispatch(new RecordVideoMessage($bug->getId()));
        if ($this->config->shouldReportBug()) {
            $this->messageBus->dispatch(new ReportBugMessage($bug->getId()));
        }
    }
}
