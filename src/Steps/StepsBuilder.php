<?php

namespace Tienvx\Bundle\MbtBundle\Steps;

use Exception;
use Tienvx\Bundle\MbtBundle\Steps\BuilderStrategy\StrategyInterface;

class StepsBuilder
{
    /**
     * @var StrategyInterface
     */
    protected $strategy;

    public function setStrategy(StrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * @throws Exception
     */
    public function create(Steps $original, int $from, int $to): Steps
    {
        if (!$this->strategy instanceof StrategyInterface) {
            throw new Exception('Missing strategy');
        }

        return $this->strategy->create($original, $from, $to);
    }
}
