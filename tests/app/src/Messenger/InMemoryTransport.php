<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Messenger\Transport\TransportInterface;

class InMemoryTransport implements TransportInterface
{
    private $serializer;
    private $messageStorage;
    private $receiver;
    private $sender;

    public function __construct(Serializer $serializer, InMemoryMessageStorage $messageStorage)
    {
        $this->serializer = $serializer;
        $this->messageStorage = $messageStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function receive(callable $handler): void
    {
        ($this->receiver ?? $this->getReceiver())->receive($handler);
    }

    /**
     * {@inheritdoc}
     */
    public function stop(): void
    {
        ($this->receiver ?? $this->getReceiver())->stop();
    }

    /**
     * {@inheritdoc}
     */
    public function send(Envelope $envelope): void
    {
        ($this->sender ?? $this->getSender())->send($envelope);
    }

    private function getReceiver()
    {
        return $this->receiver = new InMemoryReceiver($this->serializer, $this->messageStorage);
    }

    private function getSender()
    {
        return $this->sender = new InMemorySender($this->serializer, $this->messageStorage);
    }
}
