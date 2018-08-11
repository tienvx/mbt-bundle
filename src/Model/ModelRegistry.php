<?php

namespace Tienvx\Bundle\MbtBundle\Model;

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

    public function addModel($name, Model $model)
    {
        $this->models[$name] = $model;
    }

    public function hasModel($name)
    {
        return isset($this->models[$name]);
    }

    /**
     * @param $name
     * @return Model
     * @throws \Exception
     */
    public function getModel($name): Model
    {
        if (isset($this->models[$name])) {
            return $this->models[$name];
        }

        throw new \Exception(sprintf('Model %s does not exist.', $name));
    }
}
