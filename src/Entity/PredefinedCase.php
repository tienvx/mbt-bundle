<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Validator\Constraints as MbtAssert;

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

    public function init(string $name, string $title, string $model, string $steps): void
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

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @MbtAssert\Model
     */
    public function getModel(): Model
    {
        return new Model($this->model);
    }

    public function setModel(Model $model): void
    {
        $this->model = $model->getName();
    }

    public function getSteps(): Steps
    {
        return Steps::deserialize($this->steps);
    }

    public function setSteps(Steps $steps): void
    {
        $this->steps = $steps->serialize();
    }
}
