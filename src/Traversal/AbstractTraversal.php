<?php

namespace Tienvx\Bundle\MbtBundle\Traversal;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Service\DataProvider;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

abstract class AbstractTraversal
{
    /**
     * @var DataProvider
     */
    protected $dataProvider;

    /**
     * @var GraphBuilder
     */
    protected $graphBuilder;

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
     * @var Directed
     */
    protected $currentEdge;

    /**
     * @var Path
     */
    protected $path;

    /**
     * @var Subject
     */
    protected $subject;

    public function __construct(DataProvider $dataProvider, GraphBuilder $graphBuilder)
    {
        $this->dataProvider = $dataProvider;
        $this->graphBuilder = $graphBuilder;
        $this->path = new Path();
    }

    public function setArgs($args)
    {
    }

    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    public function getPath(): Path
    {
        return $this->path;
    }

    public function canGoNextStep(Directed $currentEdge): bool
    {
        return false;
    }

    public function getNextStep(): ?Directed
    {
        return null;
    }

    public function goToNextStep(Directed $edge, bool $callSUT = false)
    {
    }

    public function getMaxProgress(): int
    {
        return 100;
    }

    public function getCurrentProgress(): int
    {
        return 0;
    }

    public function getCurrentProgressMessage(): string
    {
        return '';
    }

    public function meetStopCondition(): bool
    {
        return false;
    }

    public function init()
    {
        $this->graph = $this->graphBuilder->build($this->model);
        $this->currentVertex = $this->graph->getVertex($this->model->getDefinition()->getInitialPlace());

        $this->path->addVertex($this->currentVertex);

        $subjectClass = $this->model->getSubject();
        $this->subject = new $subjectClass();
    }
}
