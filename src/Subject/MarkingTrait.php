<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

trait MarkingTrait
{
    /**
     * @var mixed Required by workflow component
     */
    protected $marking;

    /**
     * @var array Required by workflow component
     */
    protected $context;

    /**
     * Required by workflow component.
     */
    public function getMarking()
    {
        return $this->marking;
    }

    /**
     * Required by workflow component.
     */
    public function setMarking($marking, array $context = []): void
    {
        $this->marking = $marking;
        $this->context = $context;
    }
}
