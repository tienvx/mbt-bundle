<?php

namespace Tienvx\Bundle\MbtBundle\Graph\Algorithm;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;

class BalanceInfo
{
    /**
     * @var array
     */
    protected $map = [];

    /**
     * instantiate new algorithm.
     *
     * @param Graph $graph Graph to operate on
     */
    public function __construct(Graph $graph)
    {
        $map = [];
        foreach ($graph->getVertices() as $vertex) {
            $map[$vertex->getId()] = $this->getBalance($vertex);
        }
        asort($map);

        $this->map = $map;
    }

    public function canBalance(): bool
    {
        return reset($this->map) < 0 && end($this->map) > 0;
    }

    public function balance(callable $callback): void
    {
        ++$this->map[$this->getFirstVertexId()];
        --$this->map[$this->getLastVertexId()];
        asort($this->map);

        $callback($this->getFirstVertexId(), $this->getLastVertexId());
    }

    protected function getBalance(Vertex $vertex): int
    {
        return $vertex->getEdgesOut()->count() - $vertex->getEdgesIn()->count();
    }

    protected function getFirstVertexId(): string
    {
        reset($this->map);

        return key($this->map);
    }

    protected function getLastVertexId(): string
    {
        end($this->map);

        return key($this->map);
    }
}
