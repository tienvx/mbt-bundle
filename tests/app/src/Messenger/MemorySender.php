<?php

namespace App\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;

class MemorySender implements SenderInterface
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function send(Envelope $envelope): Envelope
    {
        $this->connection->publish($envelope);

        return $envelope;
    }
}
