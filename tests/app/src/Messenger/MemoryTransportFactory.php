<?php

namespace App\Messenger;

use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class MemoryTransportFactory implements TransportFactoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function createTransport(string $dsn, array $options): TransportInterface
    {
        return new MemoryTransport(
            $this->connection
        );
    }

    public function supports(string $dsn, array $options): bool
    {
        return 0 === \strpos($dsn, 'memory://');
    }
}
