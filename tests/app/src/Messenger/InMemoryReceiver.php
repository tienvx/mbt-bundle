<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

use Symfony\Component\Messenger\Transport\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;

class InMemoryReceiver implements ReceiverInterface
{
    protected $serializer;
    protected $messageStorage;

    public function __construct(Serializer $serializer, InMemoryMessageStorage $messageStorage)
    {
        $this->serializer = $serializer;
        $this->messageStorage = $messageStorage;
    }

    public function receive(callable $handler) : void
    {
        foreach ($this->messageStorage->getMessages() as $message) {
            $handler($this->serializer->decode($message));
        }
    }

    public function stop(): void
    {
        // noop
    }
}
