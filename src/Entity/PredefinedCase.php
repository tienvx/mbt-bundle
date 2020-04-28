<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use Tienvx\Bundle\MbtBundle\Model\WorkflowInterface;
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
    private $workflow;

    /**
     * @var string
     */
    private $steps;

    public function init(string $name, string $title, string $workflow, string $steps): void
    {
        $this->name = $name;
        $this->title = $title;
        $this->workflow = $workflow;
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
     * @MbtAssert\Workflow
     */
    public function getWorkflow(): WorkflowInterface
    {
        return new Workflow($this->workflow);
    }

    public function setWorkflow(WorkflowInterface $workflow): void
    {
        $this->workflow = $workflow->getName();
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
