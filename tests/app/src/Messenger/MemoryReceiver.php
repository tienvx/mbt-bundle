<?php

namespace App\Messenger;

use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

class MemoryReceiver implements ReceiverInterface
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function receive(callable $handler): void
    {
        $message = $this->connection->get();
        $handler($message);
    }

    public function stop(): void
    {
    }
}
