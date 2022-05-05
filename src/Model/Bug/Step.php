<?php

namespace Tienvx\Bundle\MbtBundle\Model\Bug;

use JMGQ\AStar\Node\NodeIdentifierInterface;
use SingleColorPetrinet\Model\Color;
use SingleColorPetrinet\Model\ColorInterface;

class Step implements StepInterface, NodeIdentifierInterface
{
    protected ColorInterface $color;
    protected array $places;
    protected int $transition;

    public function __serialize(): array
    {
        return [
            'color' => $this->color->getValues(),
            'places' => $this->places,
            'transition' => $this->transition,
        ];
    }

    public function __unserialize(array $data)
    {
        $this->color = new Color($data['color']);
        $this->places = $data['places'];
        $this->transition = $data['transition'];
    }

    public function __construct(array $places, ColorInterface $color, int $transition)
    {
        $this->setPlaces($places);
        $this->setColor($color);
        $this->setTransition($transition);
    }

    public function __clone()
    {
        $this->color = clone $this->color;
    }

    public function getUniqueNodeId(): string
    {
        ksort($this->places);

        return md5(serialize($this->places));
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
