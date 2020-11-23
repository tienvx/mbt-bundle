<?php

namespace Tienvx\Bundle\MbtBundle\Model\Generator;

use Petrinet\Model\MarkingInterface;
use Petrinet\Model\TransitionInterface;

interface StateInterface
{
    public function canStop(): bool;

    public function update(MarkingInterface $marking, TransitionInterface $transition): void;

    public function getStepsCount(): int;

    public function setStepsCount(int $stepsCount): void;

    public function getMaxSteps(): int;

    public function setMaxSteps(int $maxSteps): void;

    public function getVisitedPlaces(): array;

    public function setVisitedPlaces(array $visitedPlaces): void;

    public function getTotalPlaces(): int;

    public function setTotalPlaces(int $totalPlaces): void;

    public function getVisitedTransitions(): array;

    public function setVisitedTransitions(array $visitedTransitions): void;

    public function getTotalTransitions(): int;

    public function setTotalTransitions(int $totalTransitions): void;

    public function getMaxTransitionCoverage(): float;

    public function setMaxTransitionCoverage(float $maxTransitionCoverage): void;

    public function getMaxPlaceCoverage(): float;

    public function setMaxPlaceCoverage(float $maxPlaceCoverage): void;

    public function getTransitionCoverage(): float;

    public function setTransitionCoverage(float $transitionCoverage): void;

    public function getPlaceCoverage(): float;

    public function setPlaceCoverage(float $placeCoverage): void;
}
