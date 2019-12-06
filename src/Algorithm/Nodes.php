<?php

namespace Tienvx\Bundle\MbtBundle\Algorithm;

use Exception;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Steps\Step;

class Nodes
{
    public static function toSteps(array $nodes): array
    {
        $steps = [];
        foreach ($nodes as $node) {
            if (!$node instanceof Node) {
                throw new Exception('Invalid node');
            }
            if (!$node->getParent()) {
                continue; // Ignore first node.
            }
            if (is_null($node->getTransition())) {
                throw new Exception('Missing transition in node');
            }
            $steps[] = new Step(
                $node->getTransition(),
                new Data(),
                $node->getPlaces()
            );
        }

        return $steps;
    }
}
