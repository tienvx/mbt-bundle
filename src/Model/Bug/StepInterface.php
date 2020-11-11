<?php

namespace Tienvx\Bundle\MbtBundle\Model\Bug;

use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\MarkingInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;

interface StepInterface
{
    public function setBug(BugInterface $bug): void;

    public function getBug(): BugInterface;

    public function getMarking(): MarkingInterface;

    public function setMarking(MarkingInterface $marking): void;

    public function getTransition(): ?TransitionInterface;

    public function setTransition(TransitionInterface $transition): void;
}
