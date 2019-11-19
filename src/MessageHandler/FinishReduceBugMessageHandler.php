<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Message\ApplyBugTransitionMessage;
use Tienvx\Bundle\MbtBundle\Message\CaptureScreenshotsMessage;
use Tienvx\Bundle\MbtBundle\Message\FinishReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;

class FinishReduceBugMessageHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $messageBus)
    {
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
    }

    /**
     * @throws Exception
     */
    public function __invoke(FinishReduceBugMessage $message)
    {
        $bugId = $message->getId();
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        $this->messageBus->dispatch(new ApplyBugTransitionMessage($bug->getId(), BugWorkflow::COMPLETE_REDUCE));

        $task = $bug->getTask();
        if ($task instanceof Task) {
            if (!empty($task->getReporters())) {
                foreach ($task->getReporters() as $reporter) {
                    $this->messageBus->dispatch(new ReportBugMessage($bug->getId(), $reporter->getName()));
                }
            }
            if ($task->getTakeScreenshots()) {
                $this->messageBus->dispatch(new CaptureScreenshotsMessage($bug->getId()));
            }
        }
    }
}
