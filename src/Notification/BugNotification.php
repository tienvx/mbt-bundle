<?php

namespace Tienvx\Bundle\MbtBundle\Notification;

use Exception;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Notification\ChatNotificationInterface;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Notification\SmsNotificationInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Notifier\Recipient\SmsRecipientInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;

class BugNotification extends Notification implements ChatNotificationInterface, EmailNotificationInterface, SmsNotificationInterface
{
    /**
     * @var Bug
     */
    protected $bug;

    /**
     * @var string
     */
    private $emailFrom;

    /**
     * @var string
     */
    private $adminUrl;

    public function __construct(Bug $bug, string $emailFrom, string $adminUrl, string $subject = '', array $channels = [])
    {
        $this->bug = $bug;
        $this->emailFrom = $emailFrom;
        $this->adminUrl = $adminUrl;
        parent::__construct($subject, $channels);
    }

    public function asChatMessage(Recipient $recipient, ?string $transport = null): ?ChatMessage
    {
        if ('slack' === $transport && class_exists('Symfony\Component\Notifier\Bridge\Slack\SlackOptions')) {
            return new ChatMessage($this->getSubject(), \Symfony\Component\Notifier\Bridge\Slack\SlackOptions::fromNotification($this));
        }

        return new ChatMessage($this->getSubject());
    }

    public function asEmailMessage(Recipient $recipient, ?string $transport = null): ?EmailMessage
    {
        $bugUrl = rtrim($this->adminUrl, '/').'/#/bugs/%s/show';
        $bugId = urlencode(sprintf('/api/bugs/%d', $this->bug->getId()));
        $email = (new NotificationEmail())
            ->from($this->emailFrom)
            ->to($recipient->getEmail())
            ->subject($this->getSubject())
            ->text($this->getContent())
            ->importance($this->getImportance())
            ->exception(new Exception($this->bug->getBugMessage()))
            ->action('View the bug', sprintf($bugUrl, $bugId))
        ;

        return new EmailMessage($email);
    }

    public function asSmsMessage(Recipient $recipient, ?string $transport = null): ?SmsMessage
    {
        if ($recipient instanceof SmsRecipientInterface) {
            return new SmsMessage($recipient->getPhone(), $this->getSubject());
        }

        return null;
    }

    public function getExceptionAsString(): string
    {
        return $this->bug->getBugMessage();
    }
}
