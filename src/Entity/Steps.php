<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Exception;
use Iterator;

class Steps implements Iterator
{
    /**
     * @var Step[]
     */
    protected $steps = [];

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @return Step[]
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    public function addStep(Step $step)
    {
        $this->steps[] = $step;
    }

    public function getLength(): int
    {
        return count($this->steps);
    }

    public function countUniqueTransitions(): int
    {
        $transitions = [];
        foreach ($this->steps as $step) {
            if (!in_array($step->getTransition(), $transitions)) {
                $transitions[] = $step->getTransition();
            }
        }

        return count(array_filter($transitions));
    }

    public function countUniquePlaces(): int
    {
        $places = [];
        foreach ($this->steps as $step) {
            $places = array_merge($places, $step->getPlaces());
        }

        return count(array_unique($places));
    }

    public function getPlacesAt(int $index): ?array
    {
        return isset($this->steps[$index]) ? $this->steps[$index]->getPlaces() : null;
    }

    public function setPlacesAt(int $index, array $places)
    {
        if (isset($this->steps[$index])) {
            $this->steps[$index]->setPlaces($places);
        }
    }

    /**
     * @return array
     */
    public function normalize(): array
    {
        $return = [];
        foreach ($this->getSteps() as $step) {
            $return[] = $step->normalize();
        }

        return $return;
    }

    /**
     * @param $options
     *
     * @return string
     */
    public function serialize($options = 0): string
    {
        return json_encode($this->normalize(), $options);
    }

    /**
     * @param array $steps
     *
     * @return Steps
     *
     * @throws Exception
     */
    public static function denormalize(array $steps): Steps
    {
        $return = new Steps();
        foreach ($steps as $step) {
            $return->addStep(Step::denormalize($step));
        }

        return $return;
    }

    /**
     * @param string $steps
     *
     * @return Steps
     *
     * @throws Exception
     */
    public static function deserialize(string $steps): Steps
    {
        return self::denormalize(json_decode($steps, true));
    }

    public function current()
    {
        return $this->steps[$this->position];
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
        return isset($this->steps[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;
    }
}
