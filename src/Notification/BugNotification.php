<?php

namespace Tienvx\Bundle\MbtBundle\Notification;

use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Notification\ChatNotificationInterface;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Notification\SmsNotificationInterface;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;
use Symfony\Component\Notifier\Recipient\SmsRecipientInterface;

class BugNotification extends Notification implements
    ChatNotificationInterface,
    EmailNotificationInterface,
    SmsNotificationInterface
{
    protected string $bugUrl;
    protected Address $from;

    public function setBugUrl(string $bugUrl): void
    {
        $this->bugUrl = $bugUrl;
    }

    public function setFrom(Address $from): void
    {
        $this->from = $from;
    }

    public function asChatMessage(RecipientInterface $recipient, ?string $transport = null): ?ChatMessage
    {
        $class = 'Symfony\Component\Notifier\Bridge\Slack\SlackOptions';
        if ('slack' === $transport && class_exists($class)) {
            return new ChatMessage($this->getSubject(), call_user_func([$class, 'fromNotification'], $this));
        }

        return new ChatMessage($this->getSubject());
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, ?string $transport = null): ?EmailMessage
    {
        $class = 'Symfony\Bridge\Twig\Mime\NotificationEmail';
        if ('email' === $transport && class_exists($class)) {
            $email = (new $class())
                ->from($this->from)
                ->to($recipient->getEmail())
                ->subject($this->getSubject())
                ->text($this->getContent())
                ->importance($this->getImportance())
                ->action('View the bug', $this->bugUrl);

            return new EmailMessage($email);
        }

        return null;
    }

    public function asSmsMessage(RecipientInterface $recipient, ?string $transport = null): ?SmsMessage
    {
        if ($recipient instanceof SmsRecipientInterface) {
            return new SmsMessage($recipient->getPhone(), $this->getSubject());
        }

        return null;
    }
}
