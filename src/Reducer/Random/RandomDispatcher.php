<?php

namespace Tienvx\Bundle\MbtBundle\Reducer\Random;

use Tienvx\Bundle\MbtBundle\Reducer\DispatcherTemplate;

class RandomDispatcher extends DispatcherTemplate
{
    protected function getPairs(array $steps): array
    {
        $length = count($steps);
        $maxPairs = $this->maxPairs($steps);
        $pairs = [];

        while (count($pairs) < $maxPairs) {
            $pair = array_rand(range(0, $length - 1), 2);
            if (!in_array($pair, $pairs)) {
                $pairs[] = $pair;
            }
        }

        return $pairs;
    }
}
