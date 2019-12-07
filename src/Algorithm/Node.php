<?php

namespace Tienvx\Bundle\MbtBundle\Algorithm;

use JMGQ\AStar\AbstractNode;

class Node extends AbstractNode
{
    /**
     * @var array
     */
    protected $places;

    /**
     * @var string|null
     */
    protected $transition;

    public function __construct(array $places, ?string $transition = null)
    {
        $this->places = $places;
        $this->transition = $transition;
        sort($this->places);
    }

    public function getID(): string
    {
        return implode('-', $this->places);
    }

    public function getPlaces(): array
    {
        return $this->places;
    }

    public function getTransition(): ?string
    {
        return $this->transition;
    }

    public function setTransition(string $transition): void
    {
        $this->transition = $transition;
    }
}
