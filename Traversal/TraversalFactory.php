<?php

namespace Tienvx\Bundle\MbtBundle\Traversal;

use Tienvx\Bundle\MbtBundle\Exception\TraversalException;

class TraversalFactory
{
    const RANDOM = 'random';
    const WEIGHTED_RANDOM = 'weighted-random';
    const ALL_TRANSITIONS = 'all-transitions';
    const ALL_PATH_OF_LENGTH_N = 'all-path-of-legth-n';

    public static function create($option)
    {
        preg_match('/^(.*?)\((.*?)\)$/', $option, $matches);
        $name = $matches[1];
        $args = explode(',', $matches[2]);
        switch ($name) {
            case static::RANDOM:
                return new RandomTraversal($args);
                break;
            default:
                throw new TraversalException('Algorithm is not supported');
        }
    }
}
