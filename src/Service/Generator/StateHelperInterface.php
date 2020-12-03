<?php

namespace Tienvx\Bundle\MbtBundle\Service\Generator;

use Petrinet\Model\MarkingInterface;
use Petrinet\Model\PetrinetInterface;
use Petrinet\Model\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\Generator\StateInterface;

interface StateHelperInterface
{
    public function canStop(StateInterface $state): bool;

    public function update(StateInterface $state, MarkingInterface $marking, TransitionInterface $transition): void;

    public function initState(PetrinetInterface $petrinet, array $places): StateInterface;
}
