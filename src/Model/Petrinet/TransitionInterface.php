<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Doctrine\Common\Collections\ArrayCollection;
use SingleColorPetrinet\Model\GuardedTransitionInterface as BaseTransitionInterface;

interface TransitionInterface extends BaseTransitionInterface
{
    public function setId(int $id): void;

    public function getLabel(): string;

    public function setLabel(string $label): void;

    public function getActions(): ArrayCollection;

    public function setActions(iterable $actions): void;
}
