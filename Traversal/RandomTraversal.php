<?php

namespace Tienvx\Bundle\MbtBundle\Traversal;

use Assert\Assert;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Set\Edges;
use Fhaculty\Graph\Vertex;

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

    /**
     * @var array
     */
    protected $unvisitedEdges;

    /**
     * @var array
     */
    protected $unvisitedVertices;

    public function setArgs($args)
    {
        if (!is_array($args) || count($args) !== 2 || $args[0] < 0 || $args[0] > 100 || $args[1] < 0 || $args[1] > 100) {
            throw new InvalidTraversalArguments('Invalid edge coverage or vertex coverage');
        }
        $this->edgeCoverage = $args[0];
        $this->vertexCoverage = $args[1];
        $this->visitedEdges = [];
        $this->visitedVertices = [];
        $this->currentEdgeCoverage = 0;
        $this->currentVertexCoverage = 0;
    }

    public function goToNextStep(Directed $currentEdge, bool $callSUT = false)
    {
        $transitionName = $currentEdge->getAttribute('name');

        // Update visited edges and vertices.
        if (!in_array($this->currentVertex->getId(), $this->visitedVertices)) {
            $this->visitedVertices[] = $this->currentVertex->getId();
        }
        if (!in_array($transitionName, $this->visitedEdges)) {
            $this->visitedEdges[] = $transitionName;
        }

        // Update unvisited edges and vertices.
        $allEdges = [];
        foreach ($this->graph->getEdges()->getIterator() as $edge) {
            /* @var Directed $edge */
            $allEdges[] = $edge->getAttribute('name');
        }
        $this->unvisitedEdges = array_diff($allEdges, $this->visitedEdges);
        $allVertices = [];
        foreach ($this->graph->getVertices()->getIterator() as $vertex) {
            /* @var Vertex $vertex */
            $allVertices[] = $vertex->getAttribute('name');
        }
        $this->unvisitedVertices = array_diff($allVertices, $this->visitedVertices);

        // Update progress.
        $this->currentEdgeCoverage = count($this->visitedEdges) / count($this->graph->getEdges()) * 100;
        $this->currentVertexCoverage = count($this->visitedVertices) / count($this->graph->getVertices()) * 100;
        $this->currentVertex = $currentEdge->getVertexEnd();

        // Set data to subject.
        $data = $this->dataProvider->getData($this->subject, $this->model->getName(), $transitionName);
        $this->subject->setData($data);

        // Update test sequence.
        $this->path->addEdge($currentEdge);
        $this->path->addVertex($this->currentVertex);
        $this->path->addData($data);

        // Apply model. Call SUT if needed.
        $this->subject->setCallSUT($callSUT);
        $this->model->apply($this->subject, $transitionName);
    }

    public function canGoNextStep(Directed $currentEdge): bool
    {
        return $this->model->can($this->subject, $currentEdge->getAttribute('name'));
    }

    public function getNextStep(): ?Directed
    {
        /** @var Edges $edges */
        $edges = $this->currentVertex->getEdgesOut();
        if ($edges->isEmpty()) {
            return null;
        }

        /** @var Directed $edge */
        $edge = $edges->getEdgeOrder(Edges::ORDER_RANDOM);
        return $edge;
    }

    public function getCurrentProgress(): int
    {
        return (int) floor((($this->currentEdgeCoverage > $this->edgeCoverage ? $this->edgeCoverage : $this->currentEdgeCoverage) +
            ($this->currentVertexCoverage > $this->vertexCoverage ? $this->vertexCoverage : $this->currentVertexCoverage)) /
            $this->edgeCoverage + $this->vertexCoverage);
    }

    public function getCurrentProgressMessage(): string
    {
        return sprintf('Current edge coverage: %s, vertex coverage %s', $this->currentEdgeCoverage, $this->currentVertexCoverage);
    }

    public function meetStopCondition(): bool
    {
        return $this->currentEdgeCoverage >= $this->edgeCoverage && $this->currentVertexCoverage >= $this->vertexCoverage;
    }
}
