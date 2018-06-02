<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;

class InMemorySender implements SenderInterface
{
    private $serializer;
    private $messageStorage;
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
    public function send(Envelope $envelope)
    {
        $encodedMessage = $this->serializer->encode($envelope);
        $this->messageStorage->add($this->type, $encodedMessage);
    }
}
