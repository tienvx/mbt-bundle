<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

use Symfony\Component\Messenger\Transport\Serialization\Serializer;

trait InMemoryReceiverTrait
{
    private $messageSerializer;
    private $messageStorage;

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
