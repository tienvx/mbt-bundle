<?php

namespace Tienvx\Bundle\MbtBundle\Model\Bug;

use Tienvx\Bundle\MbtBundle\Model\Petrinet\MarkingInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;

interface StepInterface
{
    public function getMarking(): MarkingInterface;

    public function setMarking(MarkingInterface $marking): void;

    public function getTransition(): ?TransitionInterface;

    public function setTransition(TransitionInterface $transition): void;
}
