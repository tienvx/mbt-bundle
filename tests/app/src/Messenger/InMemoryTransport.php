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
    private $type;

    public function __construct(Serializer $serializer, InMemoryMessageStorage $messageStorage, string $type)
    {
        $this->serializer = $serializer;
        $this->messageStorage = $messageStorage;
        $this->type = $type;
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
        return $this->receiver = new InMemoryReceiver($this->serializer, $this->messageStorage, $this->type);
    }

    private function getSender()
    {
        return $this->sender = new InMemorySender($this->serializer, $this->messageStorage, $this->type);
    }
}
