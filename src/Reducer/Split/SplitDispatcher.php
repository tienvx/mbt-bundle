<?php

namespace Tienvx\Bundle\MbtBundle\Reducer\Split;

use Tienvx\Bundle\MbtBundle\Reducer\DispatcherTemplate;

class SplitDispatcher extends DispatcherTemplate
{
    protected function getPairs(array $steps): array
    {
        $length = count($steps);
        $maxPairs = $this->maxPairs($steps);
        $pairs = [];

        $range = range(0, $length - 1, (int) ceil($length / $maxPairs));
        if (end($range) !== $length - 1) {
            $range[] = $length - 1;
        }
        for ($i = 0; $i < count($range) - 1; ++$i) {
            $pairs[] = [$range[$i], $range[$i + 1]];
        }

        return $pairs;
    }
}
