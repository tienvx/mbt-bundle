<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Message\ReducePathMessage;

class SplitPathReducer extends AbstractPathReducer
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
        $path = $bug->getPath();
        $messagesCount = 0;

        if ($path->getLength() > 2) {
            $divisor = 2;
            $quotient = floor($path->getLength() / $divisor);
            $remainder = $path->getLength() % $divisor;
            while ($quotient > 1) {
                for ($k = 0; $k < $divisor; ++$k) {
                    $i = $quotient * $k;
                    if ($k === ($divisor - 1)) {
                        $j = $quotient * ($k + 1) - 1 + $remainder;
                    } else {
                        $j = $quotient * ($k + 1) - 1;
                    }
                    $message = new ReducePathMessage($bug->getId(), static::getName(), $path->getLength(), $i, $j);
                    $this->messageBus->dispatch($message);
                    ++$messagesCount;
                    if ($messagesCount >= floor(sqrt($path->getLength()))) {
                        break 2;
                    }
                }

                ++$divisor;
                $quotient = floor($path->getLength() / $divisor);
                $remainder = $path->getLength() % $divisor;
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
