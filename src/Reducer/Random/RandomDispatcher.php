<?php

namespace Tienvx\Bundle\MbtBundle\Reducer\Random;

use Tienvx\Bundle\MbtBundle\Reducer\DispatcherTemplate;
use Tienvx\Bundle\MbtBundle\Steps\Steps;

class RandomDispatcher extends DispatcherTemplate
{
    public static function getReducerName(): string
    {
        return RandomReducer::getName();
    }

    protected function getPairs(Steps $steps): array
    {
        $max = $steps->getLength();
        $count = floor(sqrt($steps->getLength()));
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
