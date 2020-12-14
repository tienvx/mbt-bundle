<?php

namespace Tienvx\Bundle\MbtBundle\Model\Generator;

interface StateInterface
{
    public function getVisitedPlaces(): array;

    public function setVisitedPlaces(array $visitedPlaces): void;

    public function addVisitedPlace(int $placeId);

    public function getTotalPlaces(): int;

    public function setTotalPlaces(int $totalPlaces): void;

    public function getVisitedTransitions(): array;

    public function setVisitedTransitions(array $visitedTransitions): void;

    public function addVisitedTransition(int $transitionId);

    public function getTotalTransitions(): int;

    public function setTotalTransitions(int $totalTransitions): void;

    public function getTransitionCoverage(): float;

    public function setTransitionCoverage(float $transitionCoverage): void;

    public function getPlaceCoverage(): float;

    public function setPlaceCoverage(float $placeCoverage): void;
}
