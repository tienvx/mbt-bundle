<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

class Randomizer
{
    public static function randomPairs(int $max, int $count)
    {
        $pairs = [];
        while (count($pairs) < $count) {
            $pair = array_rand(range(0, $max - 1), 2);
            if (!in_array($pair, $pairs)) {
                $pairs[] = $pair;
            }
        }
        return $pairs;
    }
}
