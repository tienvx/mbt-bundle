<?php

namespace Tienvx\Bundle\MbtBundle\Steps;

use Exception;

class Steps extends StepsIterator
{
    public function addStep(Step $step): void
    {
        $this->steps[] = $step;
    }

    public function getLength(): int
    {
        return count($this->steps);
    }

    public function getPlacesAt(int $index): ?array
    {
        return isset($this->steps[$index]) ? $this->steps[$index]->getPlaces() : null;
    }

    public function setPlacesAt(int $index, array $places): void
    {
        if (isset($this->steps[$index])) {
            $this->steps[$index]->setPlaces($places);
        }
    }

    public function normalize(): array
    {
        $return = [];
        foreach ($this->steps as $step) {
            $return[] = $step->normalize();
        }

        return $return;
    }

    public function serialize(int $options = 0): string
    {
        return json_encode($this->normalize(), $options);
    }

    /**
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
     * @throws Exception
     */
    public static function deserialize(string $steps): Steps
    {
        return self::denormalize(json_decode($steps, true));
    }
}
