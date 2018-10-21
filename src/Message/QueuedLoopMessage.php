<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class QueuedLoopMessage
{
    protected $id;
    protected $length;
    protected $pair;

    public function __construct(int $id, int $length, array $pair)
    {
        $this->id     = $id;
        $this->length = $length;
        $this->pair   = $pair;
    }

    public function getId(): int
    {
        return $this->id;
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
            'id' => $this->id,
            'length' => $this->length,
            'pair' => $this->pair,
        ]);
    }

    public static function fromString(string $message)
    {
        $decoded = json_decode($message, true);
        return new static($decoded['id'], $decoded['length'], $decoded['pair']);
    }
}
