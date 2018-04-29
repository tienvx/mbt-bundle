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
        $transitionName = $currentEdge->getAttribute('name');

        $currentEdge->setAttribute('visited', true);
        foreach ($this->currentVertex->getEdgesOut() as $edge) {
            $edge->setAttribute('tried', false);
        }
        $this->currentVertex = $currentEdge->getVertexEnd();

        /** @var Directed $currentEdgeInPath */
        $currentEdgeInPath = $this->graph->getEdges()->getEdgeMatch(function (Edge $edge) use ($transitionName) {
            return $edge->getAttribute('name') === $transitionName;
        });

        // Reset data then apply model.
        $this->subject->setData([]);
        $this->subject->setCurrentEdge($currentEdgeInPath);
        $this->workflow->apply($this->subject, $transitionName);
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
