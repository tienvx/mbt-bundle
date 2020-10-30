<?php

namespace Tienvx\Bundle\MbtBundle\Model\Generator;

use Tienvx\Bundle\MbtBundle\Model\Petrinet\MarkingInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;

class State implements StateInterface
{
    protected int $stepsCount = 1;
    protected int $maxSteps;
    protected array $visitedPlaces = [];
    protected int $totalPlaces;
    protected array $visitedTransitions = [];
    protected int $totalTransitions;
    protected float $maxTransitionCoverage = 100;
    protected float $maxPlaceCoverage = 100;
    protected float $transitionCoverage = 0;
    protected float $placeCoverage = 0;

    public function __construct(int $maxSteps, array $visitedPlaces, int $totalPlaces, int $totalTransitions, float $maxTransitionCoverage, float $maxPlaceCoverage)
    {
        $this->maxSteps = $maxSteps;
        $this->visitedPlaces = $visitedPlaces;
        $this->totalPlaces = $totalPlaces;
        $this->totalTransitions = $totalTransitions;
        $this->maxTransitionCoverage = $maxTransitionCoverage;
        $this->maxPlaceCoverage = $maxPlaceCoverage;
    }

    public function canStop(): bool
    {
        return ($this->transitionCoverage >= $this->maxTransitionCoverage && $this->placeCoverage >= $this->maxPlaceCoverage) || $this->stepsCount >= $this->maxSteps;
    }

    public function update(MarkingInterface $marking, TransitionInterface $transition): void
    {
        ++$this->stepsCount;

        // Update visited places and transitions.
        foreach ($marking->getPlaceMarkings() as $placeMarking) {
            if (count($placeMarking->getTokens()) > 0 && !in_array($placeMarking->getPlace()->getId(), $this->visitedPlaces)) {
                $this->visitedPlaces[] = $placeMarking->getPlace()->getId();
            }
        }
        if (!in_array($transition->getId(), $this->visitedTransitions)) {
            $this->visitedTransitions[] = $transition->getId();
        }

        // Update current coverage.
        $this->transitionCoverage = count($this->visitedTransitions) / $this->totalTransitions * 100;
        $this->placeCoverage = count($this->visitedPlaces) / $this->totalPlaces * 100;
    }

    public function getStepsCount(): int
    {
        return $this->stepsCount;
    }

    public function setStepsCount(int $stepsCount): void
    {
        $this->stepsCount = $stepsCount;
    }

    public function getMaxSteps(): int
    {
        return $this->maxSteps;
    }

    public function setMaxSteps(int $maxSteps): void
    {
        $this->maxSteps = $maxSteps;
    }

    public function getVisitedPlaces(): array
    {
        return $this->visitedPlaces;
    }

    public function setVisitedPlaces(array $visitedPlaces): void
    {
        $this->visitedPlaces = $visitedPlaces;
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
        $this->visitedTransitions = $visitedTransitions;
    }

    public function getTotalTransitions(): int
    {
        return $this->totalTransitions;
    }

    public function setTotalTransitions(int $totalTransitions): void
    {
        $this->totalTransitions = $totalTransitions;
    }

    public function getMaxTransitionCoverage(): float
    {
        return $this->maxTransitionCoverage;
    }

    public function setMaxTransitionCoverage(float $maxTransitionCoverage): void
    {
        $this->maxTransitionCoverage = $maxTransitionCoverage;
    }

    public function getMaxPlaceCoverage(): float
    {
        return $this->maxPlaceCoverage;
    }

    public function setMaxPlaceCoverage(float $maxPlaceCoverage): void
    {
        $this->maxPlaceCoverage = $maxPlaceCoverage;
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
