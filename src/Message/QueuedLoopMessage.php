<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class QueuedLoopMessage
{
    protected $bugId;
    protected $length;
    protected $pair;

    public function __construct(int $bugId, int $length, array $pair)
    {
        $this->bugId = $bugId;
        $this->length = $length;
        $this->pair = $pair;
    }

    public function getBugId(): int
    {
        return $this->bugId;
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
            'bugId' => $this->bugId,
            'length' => $this->length,
            'pair' => $this->pair,
        ]);
    }

    public static function fromString(string $message)
    {
        $decoded = json_decode($message, true);
        return new static($decoded['bugId'], $decoded['length'], $decoded['pair']);
    }
}
