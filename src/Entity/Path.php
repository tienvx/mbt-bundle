<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Exception;

class Path
{
    /**
     * @var Step[]
     */
    protected $steps = [];

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

    public function countTransitions(): int
    {
        return count($this->steps) - 1;
    }

    public function countPlaces(): int
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

    public function getTransitionAt(int $index): ?string
    {
        return isset($this->steps[$index]) ? $this->steps[$index]->getTransition() : null;
    }

    public function getDataAt(int $index): ?StepData
    {
        return isset($this->steps[$index]) ? $this->steps[$index]->getData() : null;
    }

    public function getPlacesAt(int $index): ?array
    {
        return isset($this->steps[$index]) ? $this->steps[$index]->getPlaces() : null;
    }

    public function setTransitionAt(int $index, string $transition)
    {
        if (isset($this->steps[$index])) {
            $this->steps[$index]->setTransition($transition);
        }
    }

    public function setDataAt(int $index, StepData $data)
    {
        if (isset($this->steps[$index])) {
            $this->steps[$index]->setData($data);
        }
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
     * @return Path
     *
     * @throws Exception
     */
    public static function denormalize(array $steps): Path
    {
        $path = new Path();
        foreach ($steps as $step) {
            $path->addStep(Step::denormalize($step));
        }

        return $path;
    }

    /**
     * @param string $steps
     *
     * @return Path
     *
     * @throws Exception
     */
    public static function deserialize(string $steps): Path
    {
        return self::denormalize(json_decode($steps, true));
    }
}
