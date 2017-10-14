<?php

namespace Tienvx\Bundle\MbtBundle\Traversal;

use Assert\Assert;
use Fhaculty\Graph\Edge\Directed as DirectedBase;
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
    }

    public function run(): array
    {
        $this->graph = $this->buildGraph();
        return parent::run();
    }

    protected function getResults(): array
    {
        $this->progress->setMessage('Start traverse graph randomly');
        $this->progress->start($this->edgeCoverage + $this->vertexCoverage);
        $edgeCoverage = 0;
        $vertexCoverage = 0;
        $results = [];
        $currentVertex = $this->graph->getVertex($this->workflow->getDefinition()->getInitialPlace());
        while ($edgeCoverage < $this->edgeCoverage || $vertexCoverage < $this->vertexCoverage) {
            if (!in_array($currentVertex->getId(), $this->visitedVertices)) {
                $this->visitedVertices[] = $currentVertex->getId();
            }

            /** @var Edges $edges */
            $edges = $currentVertex->getEdgesOut();
            if ($edges->isEmpty()) {
                throw new TraversalException('Can not traverse more');
            }

            /** @var DirectedBase $edge */
            $edge = $edges->getEdgeOrder(Edges::ORDER_RANDOM);
            if (!in_array($edge->getAttribute('id'), $this->visitedEdges)) {
                $this->visitedEdges[] = $edge->getAttribute('id');
            }

            $results[] = $currentVertex->getId();
            $results[] = $edge->getAttribute('id');

            $edgeCoverage = count($this->visitedEdges) / count($this->graph->getEdges()) * 100;
            $vertexCoverage = count($this->visitedVertices) / count($this->graph->getVertices()) * 100;

            $currentVertex = $edge->getVertexEnd();

            $this->progress->setMessage(sprintf('Current edge coverage: %s, vertex coverage %s', $edgeCoverage, $vertexCoverage));
            $this->progress->setProgress(floor($edgeCoverage + $vertexCoverage));
        }
        $this->progress->finish();
        return $results;
    }
}
