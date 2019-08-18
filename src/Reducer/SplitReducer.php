<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
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
        $messagesCount = 0;

        if ($steps->getLength() > 2) {
            $divisor = 2;
            $quotient = floor($steps->getLength() / $divisor);
            $remainder = $steps->getLength() % $divisor;
            while ($quotient > 1) {
                for ($k = 0; $k < $divisor; ++$k) {
                    $i = $quotient * $k;
                    if ($k === ($divisor - 1)) {
                        $j = $quotient * ($k + 1) - 1 + $remainder;
                    } else {
                        $j = $quotient * ($k + 1) - 1;
                    }
                    $message = new ReduceStepsMessage($bug->getId(), static::getName(), $steps->getLength(), $i, $j);
                    $this->messageBus->dispatch($message);
                    ++$messagesCount;
                    if ($messagesCount >= floor(sqrt($steps->getLength()))) {
                        break 2;
                    }
                }

                ++$divisor;
                $quotient = floor($steps->getLength() / $divisor);
                $remainder = $steps->getLength() % $divisor;
            }
        }

        return $messagesCount;
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
