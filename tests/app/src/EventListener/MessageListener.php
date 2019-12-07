<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;

class MessageListener implements EventSubscriberInterface
{
    /**
     * @var array
     */
    protected $messages = [];

    public function onMessage(MessageEvent $event): void
    {
        $this->messages[] = $event->getMessage();
    }

    public function reset(): void
    {
        $this->messages = [];
    }

    public function count(): int
    {
        return count($this->messages);
    }

    public static function getSubscribedEvents()
    {
        return [
            // should be the last one to allow header changes by other listeners first
            MessageEvent::class => ['onMessage', -255],
        ];
    }
}
