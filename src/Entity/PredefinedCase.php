<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Exception;

class PredefinedCase
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $model;

    /**
     * @var string
     */
    private $steps;

    public function init(string $name, string $title, string $model, string $steps)
    {
        $this->name = $name;
        $this->title = $title;
        $this->model = $model;
        $this->steps = $steps;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @MbtAssert\Model
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return new Model($this->model);
    }

    public function setModel(Model $model)
    {
        $this->model = $model->getName();
    }

    /**
     * @return Steps
     *
     * @throws Exception
     */
    public function getSteps(): Steps
    {
        return Steps::deserialize($this->steps);
    }

    public function setSteps(Steps $steps)
    {
        $this->steps = $steps->serialize();
    }
}
