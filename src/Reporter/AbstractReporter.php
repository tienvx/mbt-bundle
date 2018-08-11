<?php

namespace Tienvx\Bundle\MbtBundle\Reporter;

use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Graph\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Model\ModelRegistry;

abstract class AbstractReporter implements ReporterInterface
{
    /**
     * @var ModelRegistry
     */
    protected $modelRegistry;

    /**
     * @var GraphBuilder
     */
    protected $graphBuilder;

    public function __construct(
        ModelRegistry $modelRegistry,
        GraphBuilder $graphBuilder
    ) {
        $this->modelRegistry = $modelRegistry;
        $this->graphBuilder = $graphBuilder;
    }

    /**
     * @param Bug $bug
     * @return array
     * @throws \Exception
     */
    protected function buildSteps(Bug $bug): array
    {
        $model = $this->modelRegistry->getModel($bug->getTask()->getModel());
        $graph = $this->graphBuilder->build($model->getDefinition());
        $path = Path::fromSteps($bug->getSteps(), $graph);

        $steps = [];
        foreach ($path->getEdges() as $index => $edge) {
            $steps[] = [
                'step' => $index + 1,
                'action' => $edge->getAttribute('label'),
                'data' => json_encode($path->getDataAt($index) ?? []),
            ];
        }
        return $steps;
    }
}
