<?php

namespace Tienvx\Bundle\MbtBundle\Graph;

use Exception;
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

    /**
     * Path constructor.
     * @param array $transitions
     * @param array $data
     * @param array $places
     * @throws Exception
     */
    public function __construct(array $transitions = [], array $data = [], array $places = [])
    {
        if (count($transitions) !== count($data) || count($transitions) !== count($places)) {
            throw new Exception('Invalid transitions, data or places for path');
        }
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

    public function countTransitions(): int
    {
        return count(array_filter($this->transitions));
    }

    public function countPlaces(): int
    {
        return count($this->places);
    }

    public function countUniqueTransitions(): int
    {
        return count(array_unique(array_filter($this->transitions)));
    }

    public function countUniquePlaces(): int
    {
        return count(array_unique(call_user_func_array('array_merge', $this->places)));
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

    public function serialize()
    {
        return serialize([$this->transitions, $this->data, $this->places]);
    }

    /**
     * @param string $serialized
     * @throws Exception
     */
    public function unserialize($serialized)
    {
        list($transitions, $data, $places) = unserialize($serialized);
        if (count($transitions) !== count($data) || count($transitions) !== count($places)) {
            throw new Exception('Invalid transitions, data or places for path');
        }
        $this->transitions = $transitions;
        $this->data = $data;
        $this->places = $places;
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
