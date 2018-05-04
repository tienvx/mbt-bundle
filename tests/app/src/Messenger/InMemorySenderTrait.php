<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Messenger;

use Symfony\Component\Messenger\Transport\Serialization\Serializer;

trait InMemorySenderTrait
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

        $this->messageStorage->add($this->type, $encodedMessage);
    }
}
