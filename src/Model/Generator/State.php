<?php

namespace Tienvx\Bundle\MbtBundle\Model\Generator;

use Tienvx\Bundle\MbtBundle\Exception\OutOfRangeException;

class State implements StateInterface
{
    protected float $transitionCoverage = 1;
    protected float $placeCoverage = 1;

    public function __construct(
        protected array $visitedPlaces = [],
        protected array $visitedTransitions = [],
        protected int $totalPlaces = 1,
        protected int $totalTransitions = 1
    ) {
        if ($totalPlaces <= 0 || $totalTransitions <= 0) {
            throw new OutOfRangeException('State need at least 1 place and 1 transition');
        }
        $this->updateCoverage();
    }

    public function addVisitedPlace(int $placeId)
    {
        if (!in_array($placeId, $this->visitedPlaces)) {
            $this->visitedPlaces[] = $placeId;
            $this->updateCoverage();
        }
    }

    public function addVisitedTransition(int $transitionId)
    {
        if (!in_array($transitionId, $this->visitedTransitions)) {
            $this->visitedTransitions[] = $transitionId;
            $this->updateCoverage();
        }
    }

    public function getTransitionCoverage(): float
    {
        return $this->transitionCoverage;
    }

    public function getPlaceCoverage(): float
    {
        return $this->placeCoverage;
    }

    protected function updateCoverage(): void
    {
        $this->transitionCoverage = count($this->visitedTransitions) / $this->totalTransitions * 100;
        $this->placeCoverage = count($this->visitedPlaces) / $this->totalPlaces * 100;
    }
}
