<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class QueuedLoopMessage
{
    protected $queuedReproducePathId;
    protected $length;
    protected $pair;

    public function __construct(int $queuedReproducePathId, int $length, array $pair)
    {
        $this->queuedReproducePathId = $queuedReproducePathId;
        $this->length = $length;
        $this->pair = $pair;
    }

    public function getQueuedReproducePathId(): int
    {
        return $this->queuedReproducePathId;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getPair(): array
    {
        return $this->pair;
    }

    public function __toString()
    {
        return json_encode([
            'queuedReproducePathId' => $this->queuedReproducePathId,
            'length' => $this->length,
            'pair' => $this->pair,
        ]);
    }

    public static function fromString(string $message)
    {
        $decoded = json_decode($message, true);
        return new static($decoded['queuedReproducePathId'], $decoded['length'], $decoded['pair']);
    }
}
