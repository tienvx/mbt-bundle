<?php

namespace Tienvx\Bundle\MbtBundle\Graph\Dumper;

use Fhaculty\Graph\Graph;

interface DumperInterface
{
    /**
     * Dumps a graph.
     *
     * @param string $initialPlaces
     * @param Graph $graph
     * @param array $options
     *
     * @return string
     */
    public function dump(string $initialPlaces, Graph $graph, array $options = array());
}
