<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\Randomizer;
use Tienvx\Bundle\MbtBundle\Message\ReducePathMessage;

class RandomPathReducer extends AbstractPathReducer
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

        if ($path->countPlaces() > 2) {
            $pairs = Randomizer::randomPairs($path->countPlaces(), floor(sqrt($path->countPlaces())));
            foreach ($pairs as $pair) {
                $message = new ReducePathMessage($bug->getId(), static::getName(), $path->countPlaces(), $pair[0], $pair[1]);
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
