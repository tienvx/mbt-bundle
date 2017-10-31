<?php

namespace Tienvx\Bundle\MbtBundle\Traversal;

use Assert\Assert;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Set\Edges;
use Fhaculty\Graph\Vertex;
use Tienvx\Bundle\MbtBundle\Exception\EmptyEdgesException;

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

    public function goToNextStep(bool $callSUT = false)
    {
        $transitionName = $this->currentEdge->getAttribute('name');

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
            /* @var $edge Directed */
            $allEdges[] = $edge->getAttribute('name');
        }
        $this->unvisitedEdges = array_diff($allEdges, $this->visitedEdges);
        $allVertices = [];
        foreach ($this->graph->getVertices()->getIterator() as $vertex) {
            /* @var $vertex Vertex */
            $allVertices[] = $vertex->getAttribute('name');
        }
        $this->unvisitedVertices = array_diff($allVertices, $this->visitedVertices);

        // Update progress.
        $this->currentEdgeCoverage = count($this->visitedEdges) / count($this->graph->getEdges()) * 100;
        $this->currentVertexCoverage = count($this->visitedVertices) / count($this->graph->getVertices()) * 100;
        $this->currentVertex = $this->currentEdge->getVertexEnd();

        // Set data to subject.
        $data = $this->dataProvider->getData($this->subject, $this->model->getName(), $transitionName);
        $this->subject->setData($data);

        // Apply model. Call SUT if needed.
        $this->subject->setCallSUT($callSUT);
        $this->model->apply($this->subject, $transitionName);

        // Update test sequence.
        array_walk($data, function (&$value, $key) {
            $value = "$key=$value";
        });
        $transitionData = implode(',', $data);
        $this->testSequence[] = "$transitionName($transitionData)";
        $placeName = $this->currentVertex->getAttribute('name');
        $this->testSequence[] = $placeName;
    }

    public function hasNextStep(): bool
    {
        /** @var Edges $edges */
        $edges = $this->currentVertex->getEdgesOut();
        return !$edges->isEmpty();
    }

    public function canGoNextStep(): bool
    {
        /** @var Edges $edges */
        $edges = $this->currentVertex->getEdgesOut();
        if ($edges->isEmpty()) {
            throw new EmptyEdgesException('Can not get next step: there are no edges to choose from.');
        }

        /** @var Directed $edge */
        $edge = $edges->getEdgeOrder(Edges::ORDER_RANDOM);

        $canGo = $this->model->can($this->subject, $edge->getAttribute('name'));
        if ($canGo) {
            // Prepare for jumping to the next step.
            $this->currentEdge = $edge;
        }
        return $canGo;
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
