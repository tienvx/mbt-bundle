<?php

namespace Tienvx\Bundle\MbtBundle\Service;

class ModelRegistry
{
    /**
     * @var array
     */
    protected $models;

    public function __construct()
    {
        $this->models = [];
    }

    public function addModel($name, array $metadata)
    {
        $this->models[$name] = $metadata;
    }

    public function hasModel($name)
    {
        return isset($this->models[$name]);
    }

    /**
     * @param $name
     * @return array
     * @throws \Exception
     */
    public function getModel($name): array
    {
        if (isset($this->models[$name])) {
            return $this->models[$name];
        }

        throw new \Exception(sprintf('Model %s does not exist.', $name));
    }
}
