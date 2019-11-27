<?php

namespace App\Mailer;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

final class InMemoryTransport extends AbstractTransport
{
    /**
     * @var array
     */
    protected $messages = [];

    protected function doSend(SentMessage $message): void
    {
        $this->messages[] = $message;
    }

    public function __toString(): string
    {
        return 'in-memory://';
    }

    public function count(): int
    {
        return count($this->messages);
    }

    public function reset(): void
    {
        $this->messages = [];
    }
}
