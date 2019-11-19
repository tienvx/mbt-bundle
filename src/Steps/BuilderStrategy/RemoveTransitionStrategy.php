<?php

namespace Tienvx\Bundle\MbtBundle\Steps\BuilderStrategy;

use Tienvx\Bundle\MbtBundle\Steps\Steps;

class RemoveTransitionStrategy implements StrategyInterface
{
    public function create(Steps $original, int $from, int $to): Steps
    {
        $fromPlaces = $original->getPlacesAt($from);
        $toPlaces = $original->getPlacesAt($to);
        if (!$fromPlaces || !$toPlaces ||
            count($fromPlaces) <= 1 || count($toPlaces) <= 1 ||
            1 !== count(array_diff($fromPlaces, $toPlaces)) ||
            1 !== count(array_diff($toPlaces, $fromPlaces))) {
            throw new Exception('Can not create new steps without transition');
        }

        $replaceStrategy = new ReplaceStrategy([]);
        $steps = $replaceStrategy->create($original, $from, $to);

        $find = array_values(array_diff($toPlaces, $fromPlaces))[0];
        $replace = array_values(array_diff($fromPlaces, $toPlaces))[0];
        // Replace from '$to'
        $this->findAndReplace($steps, $find, $replace, $to);

        return $steps;
    }

    protected function findAndReplace(Steps $steps, string $find, string $replace, int $from)
    {
        for ($i = $from; $i < $steps->getLength(); ++$i) {
            $places = $steps->getPlacesAt($i);
            $key = array_search($find, $places);
            $newPlaces = array_replace($places, [$key => $replace]);
            $steps->setPlacesAt($i, $newPlaces);
        }
    }
}
