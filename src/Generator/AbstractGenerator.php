<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Service\DataProvider;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Service\StopConditionManager;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

abstract class AbstractGenerator implements GeneratorInterface
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
     * @var StopConditionManager
     */
    protected $stopConditionManager;

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

    public function __construct(DataProvider $dataProvider, GraphBuilder $graphBuilder, StopConditionManager $stopConditionManager)
    {
        $this->dataProvider = $dataProvider;
        $this->graphBuilder = $graphBuilder;
        $this->stopConditionManager = $stopConditionManager;
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
        return null;
    }

    public function goToNextStep(Directed $currentEdge, bool $callSUT = false)
    {
        $transitionName = $currentEdge->getAttribute('name');

        // Apply model. Call SUT if needed.
        $this->subject->setCallSUT($callSUT);
        $this->model->apply($this->subject, $transitionName);
    }

    public function init(array $arguments)
    {
        $this->graph = $this->graphBuilder->build($this->model);
        $this->currentVertex = $this->graph->getVertex($this->model->getDefinition()->getInitialPlace());

        $this->path = new Path();
        $this->path->addVertex($this->currentVertex);

        $subjectClass = $this->model->getSubject();
        $this->subject = new $subjectClass();
    }

    public function meetStopCondition(): bool
    {
        return false;
    }
}
