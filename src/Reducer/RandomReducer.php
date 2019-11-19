<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Steps\Steps;

class RandomReducer extends AbstractReducer
{
    /**
     * @throws Exception
     */
    public function dispatch(Bug $bug): int
    {
        $steps = $bug->getSteps();

        if ($steps->getLength() <= 2) {
            return 0;
        }

        return parent::dispatch($bug);
    }

    protected function getPairs(Steps $steps): array
    {
        return $this->randomPairs($steps->getLength(), floor(sqrt($steps->getLength())));
    }

    protected function randomPairs(int $max, int $count)
    {
        $pairs = [];
        while (count($pairs) < $count) {
            $pair = array_rand(range(0, $max - 1), 2);
            if (!in_array($pair, $pairs)) {
                $pairs[] = $pair;
            }
        }

        return $pairs;
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
