<?php

namespace Tienvx\Bundle\MbtBundle\Service\Bug;

use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManagerInterface;
use Tienvx\Bundle\MbtBundle\Repository\BugRepositoryInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;

class BugHelper implements BugHelperInterface
{
    protected ReducerManagerInterface $reducerManager;
    protected BugRepositoryInterface $bugRepository;
    protected MessageBusInterface $messageBus;
    protected BugNotifierInterface $bugNotifier;
    protected StepsRunnerInterface $stepsRunner;
    protected ConfigInterface $config;

    public function __construct(
        ReducerManagerInterface $reducerManager,
        BugRepositoryInterface $bugRepository,
        MessageBusInterface $messageBus,
        BugNotifierInterface $bugNotifier,
        StepsRunnerInterface $stepsRunner,
        ConfigInterface $config
    ) {
        $this->reducerManager = $reducerManager;
        $this->bugRepository = $bugRepository;
        $this->messageBus = $messageBus;
        $this->bugNotifier = $bugNotifier;
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

        if ($bug->isRecording()) {
            throw new RecoverableMessageHandlingException(
                sprintf('Can not record video for bug %d: bug is recording. Will retry later', $bug->getId())
            );
        }

        $this->bugRepository->startRecording($bug);
        $this->stepsRunner->run($bug->getSteps(), $bug, true);
        $this->bugRepository->stopRecording($bug);
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
