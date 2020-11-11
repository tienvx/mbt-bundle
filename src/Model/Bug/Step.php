<?php

namespace Tienvx\Bundle\MbtBundle\Model\Bug;

use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\MarkingInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;

class Step implements StepInterface
{
    protected BugInterface $bug;
    protected MarkingInterface $marking;
    protected ?TransitionInterface $transition = null;

    public function __construct(MarkingInterface $marking, ?TransitionInterface $transition = null)
    {
        $this->marking = $marking;
        $this->transition = $transition;
    }

    public function setBug(BugInterface $bug): void
    {
        $this->bug = $bug;
    }

    public function getBug(): BugInterface
    {
        return $this->bug;
    }

    public function setMarking(MarkingInterface $marking): void
    {
        $this->marking = $marking;
    }

    public function getMarking(): MarkingInterface
    {
        return $this->marking;
    }

    public function getTransition(): ?TransitionInterface
    {
        return $this->transition;
    }

    public function setTransition(TransitionInterface $transition): void
    {
        $this->transition = $transition;
    }
}
