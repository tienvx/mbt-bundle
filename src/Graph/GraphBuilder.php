<?php

namespace Tienvx\Bundle\MbtBundle\Graph;

use Exception;
use Fhaculty\Graph\Graph;
use Tienvx\Bundle\MbtBundle\Graph\BuilderStrategy\StrategyInterface;

class GraphBuilder
{
    /**
     * @var StrategyInterface
     */
    protected $strategy;

    public function setStrategy(StrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

    public function build(): Graph
    {
        if (!$this->strategy instanceof StrategyInterface) {
            throw new Exception('Missing strategy');
        }

        return $this->strategy->build();
    }
}
