<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Helper\Randomizer;

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

        if ($steps->getLength() <= 2) {
            return 0;
        }

        return parent::dispatch($bug);
    }

    protected function getPairs(Steps $steps): array
    {
        return Randomizer::randomPairs($steps->getLength(), floor(sqrt($steps->getLength())));
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
