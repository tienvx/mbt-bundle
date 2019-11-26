<?php

namespace App\Mailer;

use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class InMemoryTransportFactory extends AbstractTransportFactory
{
    /**
     * @var InMemoryTransport
     */
    protected $transport;

    public function create(Dsn $dsn): TransportInterface
    {
        if ('in-memory' === $dsn->getScheme()) {
            if (!$this->transport instanceof InMemoryTransport) {
                $this->transport = new InMemoryTransport($this->dispatcher, $this->logger);
            }

            return $this->transport;
        }

        throw new UnsupportedSchemeException($dsn, 'in-memory', $this->getSupportedSchemes());
    }

    public function count(): int
    {
        return $this->transport instanceof InMemoryTransport ? $this->transport->count() : 0;
    }

    public function reset(): void
    {
        if ($this->transport instanceof InMemoryTransport) {
            $this->transport->reset();
        }
    }

    protected function getSupportedSchemes(): array
    {
        return ['in-memory'];
    }
}
