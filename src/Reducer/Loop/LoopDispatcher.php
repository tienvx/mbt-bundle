<?php

namespace Tienvx\Bundle\MbtBundle\Reducer\Loop;

use Tienvx\Bundle\MbtBundle\Reducer\DispatcherTemplate;
use Tienvx\Bundle\MbtBundle\Steps\Steps;

class LoopDispatcher extends DispatcherTemplate
{
    public static function getReducerName(): string
    {
        return LoopReducer::getName();
    }

    protected function getPairs(Steps $steps): array
    {
        $pairs = $this->getAllPairs($steps);
        $length = $steps->getLength();

        if (count($pairs) - $length < 0) {
            // If number of pairs is small enough, we handle all pairs
            return $pairs;
        }

        // If number of pairs is large, we handle a bit of easy pairs, and a bit of hard pairs
        // Hard pairs sit at the beginning of the array, easy at the end.
        $total = count($pairs);
        $limitEasy = $length;
        $easy = array_slice($pairs, -$limitEasy, $limitEasy);
        $hard = array_slice($pairs, -$total, $total - $limitEasy);
        $limitHard = floor(sqrt($length));

        return array_merge($easy, $this->randomPairs($hard, $limitHard));
    }

    protected function getAllPairs(Steps $steps): array
    {
        $pairs = [];
        $length = $steps->getLength();

        for ($i = 0; $i < $length - 1; ++$i) {
            for ($j = $i + 1; $j < $length; ++$j) {
                if (!array_diff($steps->getPlacesAt($i), $steps->getPlacesAt($j)) &&
                    !array_diff($steps->getPlacesAt($j), $steps->getPlacesAt($i))) {
                    $distance = $j - $i;
                    $pairs[] = [$i, $j, $distance];
                }
            }
        }

        usort($pairs, static function ($a, $b) {
            // Sort by distance ascending
            return $a[2] - $b[2];
        });

        return $pairs;
    }

    protected function randomPairs(array $pairs, int $limit): array
    {
        $randomPairs = [];
        while (count($randomPairs) < $limit && count($pairs) > 0) {
            $key = array_rand($pairs);
            $pair = $pairs[$key];
            // [...] is replacement of list(...)
            [$i, $j] = $pair;
            // $pair will be removed from $pairs too
            $pairs = array_filter($pairs, static function (array $pair) use ($i, $j) {
                return $pair[1] <= $i || $pair[0] >= $j;
            });
            $randomPairs[] = $pair;
        }

        return $randomPairs;
    }
}
