<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Tienvx\Bundle\MbtBundle\Exception\TraversalNotSupportedException;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Traversal\AbstractTraversal;
use Tienvx\Bundle\MbtBundle\Traversal\RandomTraversal;

class TraversalFactory
{
    /**
     * @var DataProvider
     */
    protected $dataProvider;

    /**
     * @var GraphBuilder
     */
    protected $graphBuilder;

    public function __construct(DataProvider $dataProvider, GraphBuilder $graphBuilder)
    {
        $this->dataProvider = $dataProvider;
        $this->graphBuilder = $graphBuilder;
    }

    const RANDOM = 'random';
    const WEIGHTED_RANDOM = 'weighted-random';
    const ALL_TRANSITIONS = 'all-transitions';
    const ALL_PATH_OF_LENGTH_N = 'all-path-of-legth-n';

    public function get(ContainerInterface $container, $option, Model $model): AbstractTraversal
    {
        preg_match('/^(.*?)\((.*?)\)$/', $option, $matches);
        $name = $matches[1];
        $args = explode(',', $matches[2]);

        switch ($name) {
            case static::RANDOM:
                $traversal = new RandomTraversal($this->dataProvider, $this->graphBuilder);
                break;
            default:
                throw new TraversalNotSupportedException('Traversal is not supported');
        }

        $traversal->setArgs($args);
        $traversal->setModel($model);
        $traversal->init();

        return $traversal;
    }
}