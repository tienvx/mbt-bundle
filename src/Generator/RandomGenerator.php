<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Set\Edges;
use Fhaculty\Graph\Vertex;
use Tienvx\Bundle\MbtBundle\StopCondition\StopConditionInterface;

class RandomGenerator extends AbstractGenerator
{
    /**
     * @var StopConditionInterface
     */
    protected $stopCondition;

    /**
     * @var int
     */
    protected $edgeCoverage = 0;

    /**
     * @var int
     */
    protected $vertexCoverage = 0;

    /**
     * @var array
     */
    protected $visitedEdges = [];

    /**
     * @var array
     */
    protected $visitedVertices = [];

    /**
     * @var array
     */
    protected $unvisitedEdges = [];

    /**
     * @var array
     */
    protected $unvisitedVertices = [];

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
        $this->edgeCoverage   = count($this->visitedEdges) / count($this->graph->getEdges()) * 100;
        $this->vertexCoverage = count($this->visitedVertices) / count($this->graph->getVertices()) * 100;
        $this->currentVertex  = $currentEdge->getVertexEnd();

        // Apply model. Call SUT if needed.
        $this->subject->setCallSUT($callSUT);
        $this->model->apply($this->subject, $transitionName);
    }

    public function canGoNextStep(Directed $currentEdge): bool
    {
        $transitionName = $currentEdge->getAttribute('name');

        // Set data to subject.
        $data = $this->dataProvider->getData($this->subject, $this->model->getName(), $transitionName);
        $this->subject->setData($data);

        $canGo = $this->model->can($this->subject, $currentEdge->getAttribute('name'));

        if ($canGo) {
            // Update test sequence.
            $this->path->addEdge($currentEdge);
            $this->path->addVertex($currentEdge->getVertexEnd());
            $this->path->addData($data);
        }

        return $canGo;
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

    public function meetStopCondition(): bool
    {
        return $this->stopCondition->meet([
            'edgeCoverage' => $this->edgeCoverage,
            'vertexCoverage' => $this->vertexCoverage,
        ]);
    }

    public static function getName()
    {
        return 'random';
    }

    public function init(array $arguments)
    {
        parent::init($arguments);

        $this->stopCondition = $this->stopConditionManager->getStopCondition('coverage');
        $this->stopCondition->setArguments($arguments);
    }
}
