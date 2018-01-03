<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Exception\TraversalNotSupportedException;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Generator\AbstractGenerator;
use Tienvx\Bundle\MbtBundle\Generator\RandomGenerator;

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

    public function get($option, Model $model): AbstractGenerator
    {
        preg_match('/^(.*?)\((.*?)\)$/', $option, $matches);
        $name = $matches[1];
        $args = explode(',', $matches[2]);

        switch ($name) {
            case static::RANDOM:
                $traversal = new RandomGenerator($this->dataProvider, $this->graphBuilder);
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