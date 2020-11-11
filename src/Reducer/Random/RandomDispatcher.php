<?php

namespace Tienvx\Bundle\MbtBundle\Reducer\Random;

use Doctrine\Common\Collections\Collection;
use Tienvx\Bundle\MbtBundle\Reducer\DispatcherTemplate;

class RandomDispatcher extends DispatcherTemplate
{
    protected function getPairs(Collection $steps): array
    {
        $length = $steps->count();
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
