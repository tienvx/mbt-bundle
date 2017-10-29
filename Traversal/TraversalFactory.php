<?php

namespace Tienvx\Bundle\MbtBundle\Traversal;

use Tienvx\Bundle\MbtBundle\Exception\TraversalException;
use Tienvx\Bundle\MbtBundle\Model\Model;

class TraversalFactory
{
    const RANDOM = 'random';
    const WEIGHTED_RANDOM = 'weighted-random';
    const ALL_TRANSITIONS = 'all-transitions';
    const ALL_PATH_OF_LENGTH_N = 'all-path-of-legth-n';

    public static function create($option, Model $model)
    {
        preg_match('/^(.*?)\((.*?)\)$/', $option, $matches);
        $name = $matches[1];
        $args = explode(',', $matches[2]);
        switch ($name) {
            case static::RANDOM:
                $traversal = new RandomTraversal($args);
                break;
            default:
                throw new TraversalException('Algorithm is not supported');
        }
        $traversal->setModel($model);
        $traversal->init();
        return $traversal;
    }
}
