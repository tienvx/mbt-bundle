<?php

namespace Tienvx\Bundle\MbtBundle\Model\Search;

use JMGQ\AStar\AbstractNode;
use SingleColorPetrinet\Model\ColorInterface;

class Node extends AbstractNode
{
    protected array $places;
    protected ColorInterface $color;
    protected int $transition;

    public function __construct(array $places, ColorInterface $color, int $transition)
    {
        $this->setPlaces($places);
        $this->setColor($color);
        $this->setTransition($transition);
    }

    public function getID(): string
    {
        ksort($this->places);
        $colorValues = $this->color->getValues();
        ksort($colorValues);

        return md5(serialize([
            'places' => $this->places,
            'color' => $colorValues,
        ]));
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

    public function setColor(ColorInterface $color): void
    {
        $this->color = $color;
    }

    public function getColor(): ColorInterface
    {
        return $this->color;
    }

    public function setTransition(int $transition): void
    {
        $this->transition = $transition;
    }

    public function getTransition(): ?int
    {
        return $this->transition;
    }
}
