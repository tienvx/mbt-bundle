<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\Randomizer;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;

class RandomReducer extends AbstractReducer
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
            $pairs = Randomizer::randomPairs($steps->getLength(), floor(sqrt($steps->getLength())));
            foreach ($pairs as $pair) {
                $message = new ReduceStepsMessage($bug->getId(), static::getName(), $steps->getLength(), $pair[0], $pair[1]);
                $this->messageBus->dispatch($message);
                ++$messagesCount;
            }
        }

        return $messagesCount;
    }

    public static function getName(): string
    {
        return 'random';
    }

    public function getLabel(): string
    {
        return 'Random';
    }
}
