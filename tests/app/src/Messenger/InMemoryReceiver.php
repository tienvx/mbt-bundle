<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

use Symfony\Component\Messenger\Transport\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;

abstract class InMemoryReceiver implements ReceiverInterface
{
    protected $messageSerializer;
    protected $messageStorage;
    protected $type;

    public function __construct(Serializer $messageSerializer, InMemoryMessageStorage $messageStorage)
    {
        $this->messageSerializer = $messageSerializer;
        $this->messageStorage = $messageStorage;
    }

    public function receive(callable $handler) : void
    {
        foreach ($this->messageStorage->getMessages($this->type) as $message) {
            $handler($this->messageSerializer->decode($message));
        }
    }

    public function stop(): void
    {
        // noop
    }
}
