<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Message\ApplyBugTransitionMessage;
use Tienvx\Bundle\MbtBundle\Message\ApplyTaskTransitionMessage;
use Tienvx\Bundle\MbtBundle\Message\CaptureScreenshotsMessage;
use Tienvx\Bundle\MbtBundle\Message\CreateBugMessage;
use Tienvx\Bundle\MbtBundle\Message\FinishReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Message\FinishReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;

trait MessageTrait
{
    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    protected function createBug(string $title, Steps $steps, string $bugMessage, ?int $taskId, string $model)
    {
        $message = new CreateBugMessage(
            $title,
            $steps->serialize(),
            $bugMessage,
            $taskId,
            BugWorkflow::NEW,
            $model
        );
        $this->messageBus->dispatch($message);
    }

    protected function applyTaskTransition(int $taskId, string $transition)
    {
        $this->messageBus->dispatch(new ApplyTaskTransitionMessage($taskId, $transition));
    }

    protected function applyBugTransition(int $bugId, string $transition)
    {
        $this->messageBus->dispatch(new ApplyBugTransitionMessage($bugId, $transition));
    }

    protected function reportBug(int $bugId, string $reporter)
    {
        $this->messageBus->dispatch(new ReportBugMessage($bugId, $reporter));
    }

    protected function captureScreenshots(int $bugId)
    {
        $this->messageBus->dispatch(new CaptureScreenshotsMessage($bugId));
    }

    protected function finishReduceBug(int $bugId)
    {
        $this->messageBus->dispatch(new FinishReduceBugMessage($bugId));
    }

    protected function finishReduceSteps(Bug $bug): void
    {
        $this->messageBus->dispatch(new FinishReduceStepsMessage($bug->getId()));
    }
}
