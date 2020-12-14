<?php

namespace Tienvx\Bundle\MbtBundle\Model\Generator;

class State implements StateInterface
{
    protected array $visitedPlaces = [];
    protected int $totalPlaces;
    protected array $visitedTransitions = [];
    protected int $totalTransitions;
    protected float $transitionCoverage = 0;
    protected float $placeCoverage = 0;

    public function __construct(
        array $visitedPlaces,
        int $totalPlaces,
        int $totalTransitions
    ) {
        $this->visitedPlaces = $visitedPlaces;
        $this->totalPlaces = $totalPlaces;
        $this->totalTransitions = $totalTransitions;
    }

    public function getVisitedPlaces(): array
    {
        return $this->visitedPlaces;
    }

    public function setVisitedPlaces(array $visitedPlaces): void
    {
        $this->visitedPlaces = [];

        foreach ($visitedPlaces as $visitedPlace) {
            $this->addVisitedPlace($visitedPlace);
        }
    }

    public function addVisitedPlace(int $placeId)
    {
        if (!in_array($placeId, $this->visitedPlaces)) {
            $this->visitedPlaces[] = $placeId;
        }
    }

    public function getTotalPlaces(): int
    {
        return $this->totalPlaces;
    }

    public function setTotalPlaces(int $totalPlaces): void
    {
        $this->totalPlaces = $totalPlaces;
    }

    public function getVisitedTransitions(): array
    {
        return $this->visitedTransitions;
    }

    public function setVisitedTransitions(array $visitedTransitions): void
    {
        $this->visitedTransitions = [];

        foreach ($visitedTransitions as $visitedTransition) {
            $this->addVisitedTransition($visitedTransition);
        }
    }

    public function addVisitedTransition(int $transitionId)
    {
        if (!in_array($transitionId, $this->visitedTransitions)) {
            $this->visitedTransitions[] = $transitionId;
        }
    }

    public function getTotalTransitions(): int
    {
        return $this->totalTransitions;
    }

    public function setTotalTransitions(int $totalTransitions): void
    {
        $this->totalTransitions = $totalTransitions;
    }

    public function getTransitionCoverage(): float
    {
        return $this->transitionCoverage;
    }

    public function setTransitionCoverage(float $transitionCoverage): void
    {
        $this->transitionCoverage = $transitionCoverage;
    }

    public function getPlaceCoverage(): float
    {
        return $this->placeCoverage;
    }

    public function setPlaceCoverage(float $placeCoverage): void
    {
        $this->placeCoverage = $placeCoverage;
    }
}
