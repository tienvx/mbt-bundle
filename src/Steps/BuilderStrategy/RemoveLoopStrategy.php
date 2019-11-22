<?php

namespace Tienvx\Bundle\MbtBundle\Steps\BuilderStrategy;

use Exception;
use Tienvx\Bundle\MbtBundle\Steps\Steps;

class RemoveLoopStrategy implements StrategyInterface
{
    public function create(Steps $original, int $from, int $to): Steps
    {
        $fromPlaces = $original->getPlacesAt($from);
        $toPlaces = $original->getPlacesAt($to);
        if (!$fromPlaces || !$toPlaces ||
            array_diff($fromPlaces, $toPlaces) ||
            array_diff($toPlaces, $fromPlaces)) {
            throw new Exception('Can not create new path without loop');
        }

        $replaceStrategy = new ReplaceStrategy([]);

        return $replaceStrategy->create($original, $from, $to);
    }
}
