<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Notification\BugNotification;
use Tienvx\Bundle\MbtBundle\Service\NotifyHelperInterface;

/**
 * Override this service to customize notification.
 */
class ReportBugMessageHandler implements MessageHandlerInterface
{
    protected EntityManagerInterface $entityManager;
    protected NotifierInterface $notifier;
    protected TranslatorInterface $translator;
    protected NotifyHelperInterface $notifyHelper;

    public function __construct(
        EntityManagerInterface $entityManager,
        NotifierInterface $notifier,
        TranslatorInterface $translator,
        NotifyHelperInterface $notifyHelper
    ) {
        $this->entityManager = $entityManager;
        $this->notifier = $notifier;
        $this->translator = $translator;
        $this->notifyHelper = $notifyHelper;
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(ReportBugMessage $message): void
    {
        $bugId = $message->getBugId();

        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug instanceof BugInterface) {
            throw new UnexpectedValueException(sprintf('Can not report bug %d: bug not found', $bugId));
        }

        if ($bug->getTask()->getTaskConfig()->getNotifyChannels()) {
            $this->sendNotification($bug);
        }
    }

    protected function sendNotification(BugInterface $bug): void
    {
        $notification = new BugNotification($bug->getTitle(), $bug->getTask()->getTaskConfig()->getNotifyChannels());
        $notification->setBugUrl($this->notifyHelper->getBugUrl($bug));
        $notification->setFrom($this->notifyHelper->getFromAddress());
        $notification->content(implode("\n", [
            $this->translator->trans('mbt.notify.bug_id', ['%id%' => $bug->getId()]),
            $this->translator->trans('mbt.notify.bug_message', ['%message%' => $bug->getMessage()]),
            $this->translator->trans('mbt.notify.more_info'),
        ]));
        $notification->emoji(':bug:');

        $this->notifier->send($notification, ...$this->getRecipients($bug->getTask()));
    }

    protected function getRecipients(TaskInterface $task): array
    {
        if ($task->getTaskConfig()->getNotifyAuthor() && $task->getAuthor()) {
            return [$this->notifyHelper->getRecipient($task->getAuthor())];
        }

        return [];
    }
}
