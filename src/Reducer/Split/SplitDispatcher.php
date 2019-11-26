<?php

namespace Tienvx\Bundle\MbtBundle\Reducer\Split;

use Tienvx\Bundle\MbtBundle\Reducer\DispatcherTemplate;
use Tienvx\Bundle\MbtBundle\Steps\Steps;

class SplitDispatcher extends DispatcherTemplate
{
    public static function getReducerName(): string
    {
        return SplitReducer::getName();
    }

    protected function getPairs(Steps $steps): array
    {
        $pairs = [];
        $divisor = 1;
        do {
            ++$divisor;
            $quotient = floor($steps->getLength() / $divisor);
            if ($quotient <= 1) {
                break;
            }
            $pairs += $this->divide($steps, $divisor, $quotient);
        } while (count($pairs) < floor(sqrt($steps->getLength())));

        return $pairs;
    }

    protected function divide(Steps $steps, int $divisor, int $quotient): array
    {
        $pairs = [];
        $remainder = $steps->getLength() % $divisor;
        for ($k = 0; $k < $divisor; ++$k) {
            $i = $quotient * $k;
            if ($k === ($divisor - 1)) {
                $j = $quotient * ($k + 1) - 1 + $remainder;
            } else {
                $j = $quotient * ($k + 1) - 1;
            }
            $pairs[] = [$i, $j];
        }

        return $pairs;
    }
}
