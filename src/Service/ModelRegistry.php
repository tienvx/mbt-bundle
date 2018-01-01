<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Model\Model;

class ModelRegistry
{
    /**
     * @var Model[]
     */
    protected $models;

    public function __construct()
    {
        $this->models = [];
    }

    public function add($name, Model $model)
    {
        $this->models[$name] = $model;
    }

    public function has($name)
    {
        return isset($this->models[$name]);
    }

    public function get($name)
    {
        return $this->models[$name];
    }
}
