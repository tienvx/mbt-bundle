<?php

namespace Tienvx\Bundle\MbtBundle\Reducer\Transition;

use Tienvx\Bundle\MbtBundle\Reducer\DispatcherTemplate;
use Tienvx\Bundle\MbtBundle\Steps\Steps;

class TransitionDispatcher extends DispatcherTemplate
{
    public static function getReducerName(): string
    {
        return TransitionReducer::getName();
    }

    protected function getPairs(Steps $steps): array
    {
        $pairs = [];
        $length = $steps->getLength();

        for ($i = 0; $i < $length - 1; ++$i) {
            $j = $i + 1;
            $fromPlaces = $steps->getPlacesAt($i);
            $toPlaces = $steps->getPlacesAt($j);
            // Workflow only, does not work with state machine
            if ($fromPlaces && $toPlaces &&
                count($fromPlaces) > 1 && count($toPlaces) > 1 &&
                1 === count(array_diff($fromPlaces, $toPlaces)) &&
                1 === count(array_diff($toPlaces, $fromPlaces))) {
                $pairs[] = [$i, $j];
            }
        }

        return $pairs;
    }
}
