<?php

namespace Tienvx\Bundle\MbtBundle\Generator\Predefined;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Graphp\Algorithms\ConnectedComponents;
use Tienvx\Bundle\MbtBundle\Graph\Algorithm\Eulerian;

class AllTransitionsGenerator extends PredefinedGeneratorTemplate
{
    public static function getName(): string
    {
        return 'all-transitions';
    }

    public function getLabel(): string
    {
        return 'All Transitions';
    }

    public static function support(): bool
    {
        return true;
    }

    protected function getPredefinedEdges(Graph $graph, Vertex $startVertex): array
    {
        if ($this->singleComponent($graph)) {
            $algorithm = new Eulerian($graph);
            $edges = $algorithm->getEdges($startVertex);

            return $edges->getVector();
        }

        return [];
    }

    protected function singleComponent(Graph $graph): bool
    {
        $components = new ConnectedComponents($graph);

        return $components->isSingle();
    }
}
