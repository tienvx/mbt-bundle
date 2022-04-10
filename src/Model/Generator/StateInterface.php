<?php

namespace Tienvx\Bundle\MbtBundle\Model\Generator;

interface StateInterface
{
    public function addVisitedPlace(int $placeId);

    public function addVisitedTransition(int $transitionId);

    public function getTransitionCoverage(): float;

    public function getPlaceCoverage(): float;
}
