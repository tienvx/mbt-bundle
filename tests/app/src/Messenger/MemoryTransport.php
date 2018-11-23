<?php

namespace App\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;

class MemoryTransport implements TransportInterface
{
    private $connection;
    private $receiver;
    private $sender;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function receive(callable $handler): void
    {
        ($this->receiver ?? $this->getReceiver())->receive($handler);
    }

    public function stop(): void
    {
        ($this->receiver ?? $this->getReceiver())->stop();
    }

    public function send(Envelope $envelope): Envelope
    {
        return ($this->sender ?? $this->getSender())->send($envelope);
    }

    private function getReceiver()
    {
        return $this->receiver = new MemoryReceiver($this->connection);
    }

    private function getSender()
    {
        return $this->sender = new MemorySender($this->connection);
    }
}
