<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Set\Edges;
use Tienvx\Bundle\MbtBundle\Helper\Randomizer;

class WeightedRandomGenerator extends RandomGenerator
{
    public function getNextStep(): ?Directed
    {
        /** @var Edges $edges */
        $edges = $this->currentVertex->getEdgesOut();
        if ($edges->isEmpty()) {
            return null;
        }

        $edgesByWeight = [];
        /** @var Directed $edge */
        foreach ($edges as $index => $edge) {
            $edgesByWeight[$index] = $edge->getWeight();
        }
        /** @var Directed $edge */
        $index = Randomizer::randomByWeight($edgesByWeight);
        /** @var Directed $edge */
        $edge = $edges->getEdgeIndex($index);
        return $edge;
    }

    public static function getName()
    {
        return 'weighted-random';
    }
}
