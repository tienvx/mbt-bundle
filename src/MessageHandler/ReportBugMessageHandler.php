<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Notification\BugNotification;

/**
 * Override this service to customize notification.
 */
class ReportBugMessageHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var NotifierInterface
     */
    private $notifier;

    /**
     * @var string
     */
    private $emailFrom;

    /**
     * @var string
     */
    private $adminUrl;

    public function __construct(EntityManagerInterface $entityManager, NotifierInterface $notifier)
    {
        $this->entityManager = $entityManager;
        $this->notifier = $notifier;
    }

    public function __invoke(ReportBugMessage $message): void
    {
        $bugId = $message->getBugId();
        $channels = $message->getChannels();

        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        $this->sendNotification($bug, $channels);
    }

    public function setEmailFrom(string $emailFrom): void
    {
        $this->emailFrom = $emailFrom;
    }

    public function setAdminUrl(string $adminUrl): void
    {
        $this->adminUrl = $adminUrl;
    }

    protected function sendNotification(Bug $bug, array $channels): void
    {
        $notification = new BugNotification($bug, $this->emailFrom, $this->adminUrl, sprintf('A new bug was found (id: %d)!', $bug->getId()), $channels);
        $notification->content(implode("\n", [
            sprintf('We found a new bug during testing the model "%s"!', $bug->getModel()->getName()),
            sprintf('Bug id: %d', $bug->getId()),
            sprintf('Bug title: %s', $bug->getTitle()),
            'The reproduce steps have been reduced, and the screenshots have been captured if configured',
            'You can download exception.txt to see the bug message, or',
            'follow the action to get more information about the bug',
        ]));
        $notification->emoji(':bug:');

        foreach ($this->getRecipients() as $recipient) {
            $this->notifier->send($notification, $recipient);
        }
    }

    protected function getRecipients(): array
    {
        if ($this->notifier instanceof Notifier) {
            return $this->notifier->getAdminRecipients();
        }

        return [];
    }
}
