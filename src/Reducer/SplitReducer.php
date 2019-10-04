<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;

class SplitReducer extends AbstractReducer
{
    /**
     * @param Bug $bug
     *
     * @return int
     *
     * @throws Exception
     */
    public function dispatch(Bug $bug): int
    {
        $steps = $bug->getSteps();

        if ($steps->getLength() <= 2) {
            return 0;
        }

        $pairs = $this->getPairs($steps);

        foreach ($pairs as $pair) {
            $message = new ReduceStepsMessage($bug->getId(), static::getName(), $steps->getLength(), $pair[0], $pair[1]);
            $this->messageBus->dispatch($message);
        }

        return count($pairs);
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
        } while (count($pairs) < floor(sqrt($steps->getLength())));

        return $pairs;
    }

    public static function getName(): string
    {
        return 'split';
    }

    public function getLabel(): string
    {
        return 'Split';
    }
}
