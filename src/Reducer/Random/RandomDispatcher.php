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
            if ($pair[1] - $pair[0] >= static::MIN_PAIR_LENGTH && !in_array($pair, $pairs)) {
                $pairs[] = $pair;
            }
        }

        return $pairs;
    }

    protected function minSteps(): int
    {
        return 3;
    }

    protected function maxPairs(array $steps): int
    {
        return count($steps) <= 3 ? 1 : parent::maxPairs($steps);
    }
}
