<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

use Symfony\Component\Messenger\Transport\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Tienvx\Bundle\MbtBundle\Message\BugMessage;
use Tienvx\Bundle\MbtBundle\Message\QueuedPathReducerMessage;
use Tienvx\Bundle\MbtBundle\Message\ReproducePathMessage;
use Tienvx\Bundle\MbtBundle\Message\TaskMessage;

class InMemorySender implements SenderInterface
{
    private $messageSerializer;
    private $messageStorage;

    public function __construct(Serializer $messageSerializer, InMemoryMessageStorage $messageStorage)
    {
        $this->messageSerializer = $messageSerializer;
        $this->messageStorage = $messageStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function send($message)
    {
        $map = [
            BugMessage::class => 'bug',
            TaskMessage::class => 'task',
            ReproducePathMessage::class => 'reproduce-path',
            QueuedPathReducerMessage::class => 'queued-path-reducer',
        ];
        if (isset($map[get_class($message)])) {
            $encodedMessage = $this->messageSerializer->encode($message);
            $this->messageStorage->add($map[get_class($message)], $encodedMessage);
        }
    }
}
