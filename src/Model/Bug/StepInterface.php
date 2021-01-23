<?php

namespace Tienvx\Bundle\MbtBundle\Model\Bug;

use SingleColorPetrinet\Model\ColorInterface;

interface StepInterface
{
    public function getColor(): ColorInterface;

    public function setColor(ColorInterface $color): void;

    public function getPlaces(): array;

    public function setPlaces(array $places): void;

    public function addPlace(int $place, int $tokens): void;

    public function getTransition(): int;

    public function setTransition(int $transition): void;
}
