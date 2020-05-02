<?php

namespace Tienvx\Bundle\MbtBundle\Model;

class Workflow implements WorkflowInterface
{
    public const WORKFLOW = 'workflow';
    public const STATE_MACHINE = 'state_machine';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $type;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): WorkflowInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): WorkflowInterface
    {
        $this->label = $label;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): WorkflowInterface
    {
        $this->type = $type;

        return $this;
    }
}
