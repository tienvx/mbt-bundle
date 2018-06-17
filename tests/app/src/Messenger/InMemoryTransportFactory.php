<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class InMemoryTransportFactory implements TransportFactoryInterface
{
    private $serializer;
    private $messageStorage;

    public function __construct(Serializer $serializer, InMemoryMessageStorage $messageStorage)
    {
        $this->serializer = $serializer;
        $this->messageStorage = $messageStorage;
    }

    public function createTransport(string $dsn, array $options): TransportInterface
    {
        return new InMemoryTransport($this->serializer, $this->messageStorage);
    }

    public function supports(string $dsn, array $options): bool
    {
        return 0 === strpos($dsn, 'memory://');
    }
}
