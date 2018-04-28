<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\StateMachine;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Service\StopConditionManager;
use Tienvx\Bundle\MbtBundle\Model\Subject;

abstract class AbstractGenerator implements GeneratorInterface
{
    /**
     * @var Registry
     */
    protected $workflows;

    /**
     * @var GraphBuilder
     */
    protected $graphBuilder;

    /**
     * @var StopConditionManager
     */
    protected $stopConditionManager;

    /**
     * @var StateMachine
     */
    protected $workflow;

    /**
     * @var Graph
     */
    protected $graph;

    /**
     * @var Vertex
     */
    protected $currentVertex;

    /**
     * @var Path
     */
    protected $path;

    /**
     * @var Subject
     */
    protected $subject;

    public function __construct(Registry $workflows, GraphBuilder $graphBuilder, StopConditionManager $stopConditionManager)
    {
        $this->workflows = $workflows;
        $this->graphBuilder = $graphBuilder;
        $this->stopConditionManager = $stopConditionManager;
    }

    public function getPath(): Path
    {
        return $this->path;
    }

    public function canGoNextStep(Directed $currentEdge): bool
    {
        return $this->workflow->can($this->subject, $currentEdge->getAttribute('name'));
    }

    public function getNextStep(): ?Directed
    {
        return null;
    }

    public function goToNextStep(Directed $currentEdge)
    {
        $transitionName = $currentEdge->getAttribute('name');

        // Reset data then apply model.
        $this->subject->setData(null);
        $this->workflow->apply($this->subject, $transitionName);

        // Update test sequence.
        $this->path->addEdge($currentEdge);
        $this->path->addVertex($currentEdge->getVertexEnd());
        $this->path->addData($this->subject->getData());
    }

    public function init(string $model, string $subject, array $arguments, bool $callSUT = false)
    {
        $this->subject = new $subject();
        $this->subject->setCallSUT($callSUT);
        $this->workflow = $this->workflows->get($this->subject, $model);

        $this->graph = $this->graphBuilder->build($this->workflow->getDefinition());
        $this->currentVertex = $this->graph->getVertex($this->workflow->getDefinition()->getInitialPlace());

        $this->path = new Path();
        $this->path->addVertex($this->currentVertex);
    }

    public function meetStopCondition(): bool
    {
        return false;
    }
}
