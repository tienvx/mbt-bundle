<?php

namespace Tienvx\Bundle\MbtBundle\Model\Bug;

interface StepInterface
{
    public function getColor(): string;

    public function setColor(string $color): void;

    public function getPlaces(): array;

    public function setPlaces(array $places): void;

    public function addPlace(int $place, int $tokens): void;

    public function getTransition(): ?int;

    public function setTransition(int $transition): void;
}
