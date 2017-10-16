<?php

namespace Tienvx\Bundle\MbtBundle\Traversal;

use Assert\Assert;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Set\Edges;
use Tienvx\Bundle\MbtBundle\Exception\TraversalException;

class RandomTraversal extends AbstractTraversal
{
    /**
     * @var int
     */
    protected $edgeCoverage;

    /**
     * @var int
     */
    protected $vertexCoverage;

    /**
     * @var int
     */
    protected $currentEdgeCoverage;

    /**
     * @var int
     */
    protected $currentVertexCoverage;

    /**
     * @var array
     */
    protected $visitedEdges;

    /**
     * @var array
     */
    protected $visitedVertices;

    public function __construct($args)
    {
        Assert::that($args)->isArray()->count(2);
        Assert::that($args[0])->numeric()->between(0, 100);
        Assert::that($args[1])->numeric()->between(0, 100);
        $this->edgeCoverage = $args[0];
        $this->vertexCoverage = $args[1];
        $this->visitedEdges = [];
        $this->visitedVertices = [];
        $this->currentEdgeCoverage = 0;
        $this->currentVertexCoverage = 0;
    }

    public function getNextStep(): array
    {
        /** @var Edges $edges */
        $edges = $this->currentVertex->getEdgesOut();
        if ($edges->isEmpty()) {
            throw new TraversalException('Can not get next step: there are no edges to choose from.');
        }

        /** @var Directed $edge */
        $edge = $edges->getEdgeOrder(Edges::ORDER_RANDOM);

        if (!in_array($this->currentVertex->getId(), $this->visitedVertices)) {
            $this->visitedVertices[] = $this->currentVertex->getId();
        }
        if (!in_array($edge->getAttribute('key'), $this->visitedEdges)) {
            $this->visitedEdges[] = $edge->getAttribute('key');
        }

        $this->currentEdgeCoverage = floor(count($this->visitedEdges) / count($this->graph->getEdges()) * 100);
        $this->currentVertexCoverage = floor(count($this->visitedVertices) / count($this->graph->getVertices()) * 100);
        $this->currentVertex = $edge->getVertexEnd();

        // [vertex, edge] pair.
        return [$this->currentVertex, $edge];
    }

    public function hasNextStep(): bool
    {
        return $this->currentEdgeCoverage < $this->edgeCoverage || $this->currentVertexCoverage < $this->vertexCoverage;
    }

    public function getMaxProgress(): int
    {
        return $this->edgeCoverage + $this->vertexCoverage;
    }

    public function getCurrentProgress(): int
    {
        return ($this->currentEdgeCoverage > $this->edgeCoverage ? $this->edgeCoverage : $this->currentEdgeCoverage) +
            ($this->currentVertexCoverage > $this->vertexCoverage ? $this->vertexCoverage : $this->currentVertexCoverage);
    }

    public function getCurrentProgressMessage(): string
    {
        return sprintf('Current edge coverage: %s, vertex coverage %s', $this->currentEdgeCoverage, $this->currentVertexCoverage);
    }
}
