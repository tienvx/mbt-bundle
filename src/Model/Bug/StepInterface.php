<?php

namespace Tienvx\Bundle\MbtBundle\Model\Bug;

use Tienvx\Bundle\MbtBundle\Model\BugInterface;

interface StepInterface
{
    public function setBug(BugInterface $bug): void;

    public function getBug(): BugInterface;

    public function getColor(): string;

    public function setColor(string $color): void;

    public function getPlaces(): array;

    public function setPlaces(array $places): void;

    public function addPlace(int $place, int $tokens): void;

    public function getTransition(): ?int;

    public function setTransition(int $transition): void;
}
