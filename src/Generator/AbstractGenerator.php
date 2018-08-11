<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Graph\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Model\Subject;
use Tienvx\Bundle\MbtBundle\StopCondition\StopConditionInterface;

abstract class AbstractGenerator implements GeneratorInterface
{
    /**
     * @var GraphBuilder
     */
    protected $graphBuilder;

    /**
     * @var StopConditionInterface
     */
    protected $stopCondition;

    /**
     * @var Model
     */
    protected $model;

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

    public function __construct(GraphBuilder $graphBuilder)
    {
        $this->graphBuilder = $graphBuilder;
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function getNextStep(): ?Directed
    {
        return null;
    }

    /**
     * @param Directed $currentEdge
     * @return bool
     * @throws \Exception
     */
    public function goToNextStep(Directed $currentEdge): bool
    {
        return $this->model->applyModel($this->subject, $currentEdge, $this->path);
    }

    /**
     * @param Model $model
     * @param Subject $subject
     * @param StopConditionInterface $stopCondition
     * @throws \Exception
     */
    public function init(Model $model, Subject $subject, StopConditionInterface $stopCondition)
    {
        $this->model         = $model;
        $this->subject       = $subject;
        $this->stopCondition = $stopCondition;

        $this->graph = $this->graphBuilder->build($this->model->getDefinition());
        $this->currentVertex = $this->graph->getVertex($this->model->getDefinition()->getInitialPlace());
        $this->path = new Path();
    }

    public function meetStopCondition(): bool
    {
        return false;
    }

    public function getPath(): Path
    {
        return $this->path;
    }
}
