<?php

namespace Tienvx\Bundle\MbtBundle\Graph;

use Exception;
use Iterator;

class Path implements Iterator
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
     *
     * @param array $transitions
     * @param array $data
     * @param array $places
     *
     * @throws Exception
     */
    public function __construct(array $transitions = [], array $data = [], array $places = [])
    {
        if (count($transitions) !== count($data) || count($transitions) !== count($places)) {
            throw new Exception('Invalid transitions, data or places for path');
        }
        $this->transitions = $transitions;
        $this->data = $data;
        $this->places = $places;
        $this->position = 0;
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

    public function setPlacesAt(int $index, array $places)
    {
        $this->places[$index] = $places;
    }

    /**
     * @param Path $path
     *
     * @return array
     */
    public static function serialize(Path $path): array
    {
        $result = [];
        foreach ($path as $step) {
            $result[] = $step;
        }

        return $result;
    }

    /**
     * @param array $steps
     *
     * @return Path
     *
     * @throws Exception
     */
    public static function unserialize(array $steps): Path
    {
        $transitions = [];
        $data = [];
        $places = [];
        foreach ($steps as $step) {
            $transitions[] = $step[0];
            $data[] = $step[1];
            $places[] = $step[2];
        }

        return new Path($transitions, $data, $places);
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
