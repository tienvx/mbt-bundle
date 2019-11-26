<?php

namespace Tienvx\Bundle\MbtBundle\Steps;

use Iterator;

class StepsIterator implements Iterator
{
    /**
     * @var Step[]
     */
    protected $steps = [];

    /**
     * @var int
     */
    protected $position = 0;

    public function current()
    {
        return $this->steps[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return isset($this->steps[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }
}
