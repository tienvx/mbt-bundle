<?php

namespace Tienvx\Bundle\MbtBundle\Model\Bug;

use SingleColorPetrinet\Model\ColorInterface;

class Step implements StepInterface
{
    protected ColorInterface $color;
    protected array $places;
    protected int $transition;

    public function __construct(array $places, ColorInterface $color, int $transition)
    {
        $this->setPlaces($places);
        $this->setColor($color);
        $this->setTransition($transition);
    }

    public function setColor(ColorInterface $color): void
    {
        $this->color = $color;
    }

    public function getColor(): ColorInterface
    {
        return $this->color;
    }

    public function getPlaces(): array
    {
        return $this->places;
    }

    public function setPlaces(array $places): void
    {
        $this->places = [];

        foreach ($places as $place => $tokens) {
            $this->addPlace($place, $tokens);
        }
    }

    public function addPlace(int $place, int $tokens): void
    {
        $this->places[$place] = $tokens;
    }

    public function getTransition(): int
    {
        return $this->transition;
    }

    public function setTransition(int $transition): void
    {
        $this->transition = $transition;
    }
}
