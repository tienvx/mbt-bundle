<?php

namespace Tienvx\Bundle\MbtBundle\Model\Search;

use JMGQ\AStar\AbstractNode;

class Node extends AbstractNode
{
    protected array $places;
    protected string $color;
    protected ?int $transition = null;

    public function __construct(array $places, string $color, ?int $transition = null)
    {
        $this->setPlaces($places);
        $this->setColor($color);
        $this->setTransition($transition);
    }

    public function getID(): string
    {
        ksort($this->places);

        return md5(serialize([
            'places' => $this->places,
            'color' => $this->color,
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

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setTransition(?int $transition): void
    {
        $this->transition = $transition;
    }

    public function getTransition(): ?int
    {
        return $this->transition;
    }
}
