<?php

namespace Tienvx\Bundle\MbtBundle\Generator\Predefined;

use Fhaculty\Graph\Exception as GraphException;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Graphp\Algorithms\TravelingSalesmanProblem\Bruteforce;

class AllPlacesGenerator extends PredefinedGeneratorTemplate
{
    public static function getName(): string
    {
        return 'all-places';
    }

    public function getLabel(): string
    {
        return 'All Places';
    }

    public static function support(): bool
    {
        return true;
    }

    protected function getPredefinedEdges(Graph $graph, Vertex $startVertex): array
    {
        $algorithm = new Bruteforce($graph);
        try {
            $edges = $algorithm->getEdges();

            return $edges->getVector();
        } catch (GraphException $exception) {
            return [];
        }
    }
}
