<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Model\Model;

class ModelContainer
{
    /**
     * @var Model[]
     */
    protected $models;

    public function __construct()
    {
        $this->models = [];
    }

    public function addModel(Model $model)
    {
        $this->models[] = $model;
    }

    public function getModelChoices(): array
    {
        $choices = [];
        foreach ($this->models as $model) {
            $choices[$model->getLabel()] = $model->getName();
        }
        return $choices;
    }
}