<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

use Symfony\Component\Messenger\Transport\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;

class InMemorySender implements SenderInterface
{
    private $messageSerializer;
    private $messageStorage;

    public function __construct(Serializer $messageSerializer, InMemoryMessageStorage $messageStorage)
    {
        $this->messageSerializer = $messageSerializer;
        $this->messageStorage = $messageStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function send($message)
    {
        $encodedMessage = $this->messageSerializer->encode($message);

        $this->messageStorage->add($encodedMessage);
    }
}
