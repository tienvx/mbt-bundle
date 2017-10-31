<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Tienvx\Bundle\MbtBundle\Exception\TraversalNotSupportedException;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Traversal\AbstractTraversal;

class TraversalFactory
{
    const RANDOM = 'random';
    const WEIGHTED_RANDOM = 'weighted-random';
    const ALL_TRANSITIONS = 'all-transitions';
    const ALL_PATH_OF_LENGTH_N = 'all-path-of-legth-n';

    public function get(ContainerInterface $container, $option, Model $model): AbstractTraversal
    {
        preg_match('/^(.*?)\((.*?)\)$/', $option, $matches);
        $name = $matches[1];
        $args = explode(',', $matches[2]);

        $traversal = $container->get(sprintf('tienvx_mbt.traversal.%s', $name));
        if (!$traversal || !($traversal instanceof AbstractTraversal)) {
            throw new TraversalNotSupportedException(sprintf('Traversal "%s" is not supported', $name));
        }

        $traversal->setArgs($args);
        $traversal->setModel($model);
        $traversal->init();

        return $traversal;
    }
}