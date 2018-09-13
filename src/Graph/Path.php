<?php

namespace Tienvx\Bundle\MbtBundle\Graph;

use Iterator;
use Serializable;

class Path implements Serializable, Iterator
{
    /**
     * @var array[]
     */
    protected $data;

    /**
     * @var string[]
     */
    protected $transitions;

    /**
     * @var array[]
     */
    protected $places;

    /**
     * @var int
     */
    protected $position;

    public function __construct(array $transitions = [], array $data = [], array $places = [])
    {
        $this->transitions = $transitions;
        $this->data        = $data;
        $this->places      = $places;
        $this->position    = 0;
    }

    public function add(?string $transition, ?array $data, array $places)
    {
        $this->transitions[] = $transition;
        $this->data[] = $data;
        $this->places[] = $places;
    }

    public function countTransitions()
    {
        return count(array_filter($this->transitions));
    }

    public function countPlaces()
    {
        return count($this->places);
    }

    public function getTransitionAt(int $index): ?string
    {
        return $this->transitions[$index];
    }

    public function getDataAt(int $index): ?array
    {
        return $this->data[$index];
    }

    public function getPlacesAt(int $index): array
    {
        return $this->places[$index];
    }

    public function setDataAt(int $index, array $data)
    {
        $this->data[$index] = $data;
    }

    public function getAllTransitions(): array
    {
        return $this->transitions;
    }

    public function getAllData(): array
    {
        return $this->data;
    }

    public function getAllPlaces(): array
    {
        return $this->places;
    }

    public function serialize()
    {
        return serialize([$this->transitions, $this->data, $this->places]);
    }

    public function unserialize($serialized)
    {
        list($this->transitions, $this->data, $this->places) = unserialize($serialized);
    }

    public function current()
    {
        return [$this->transitions[$this->position], $this->data[$this->position], $this->places[$this->position]];
    }

    public function next()
    {
        ++$this->position;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return isset($this->places[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;
    }
}
