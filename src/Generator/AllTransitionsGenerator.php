<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Fhaculty\Graph\Edge\Base as Edge;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Exception\UnderflowException;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Set\Edges;
use Graphp\Algorithms\ConnectedComponents;
use Tienvx\Bundle\MbtBundle\Algorithm\Eulerian;

class AllTransitionsGenerator extends AbstractGenerator
{
    /**
     * @var bool
     */
    protected $singleComponent = false;

    /**
     * @var Graph
     */
    protected $resultGraph;

    public function init(string $model, string $subject, array $arguments, bool $callSUT = false)
    {
        parent::init($model, $subject, $arguments, $callSUT);

        $components = new ConnectedComponents($this->graph);
        $this->singleComponent = $components->isSingle();
        if ($this->singleComponent) {
            $algorithm = new Eulerian($this->graph);
            $this->resultGraph = $algorithm->getResultGraph();
            $this->currentVertex = $this->resultGraph->getVertex($this->currentVertex->getId());
        }
    }

    public function canGoNextStep(Directed $currentEdge): bool
    {
        $canGo = $this->workflow->can($this->subject, $currentEdge->getAttribute('name'));

        if ($canGo) {
            // Update test sequence.
            /** @var Directed $currentEdgeInPath */
            $currentEdgeInPath = $this->graph->getEdges()->getEdgeMatch(function (Edge $edge) use ($currentEdge) {
                return $edge->getAttribute('name') === $currentEdge->getAttribute('name');
            });
            $this->path->addEdge($currentEdgeInPath);
            $this->path->addVertex($currentEdgeInPath->getVertexEnd());
            $this->path->addData($this->subject->getData());
        }

        return $canGo;
    }

    public function getNextStep(): ?Directed
    {
        try {
            /** @var Directed $edge */
            $edge = $this->currentVertex->getEdges()->getEdgesMatch(function (Edge $edge) {
                return $edge->hasVertexStart($this->currentVertex) && !$edge->getAttribute('visited') && !$edge->getAttribute('tried');
            })->getEdgeOrder(Edges::ORDER_RANDOM);
            $edge->setAttribute('tried', true);
            return $edge;
        }
        catch (UnderflowException $e) {
            return null;
        }
    }

    public function goToNextStep(Directed $currentEdge)
    {
        $currentEdge->setAttribute('visited', true);
        foreach ($this->currentVertex->getEdgesOut() as $edge) {
            $edge->setAttribute('tried', false);
        }
        $this->currentVertex = $currentEdge->getVertexEnd();

        parent::goToNextStep($currentEdge);
    }

    public function meetStopCondition(): bool
    {
        return !$this->singleComponent;
    }

    public static function getName()
    {
        return 'all-transitions';
    }
}
