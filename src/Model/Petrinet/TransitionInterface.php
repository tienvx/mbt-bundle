<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Doctrine\Common\Collections\Collection;
use SingleColorPetrinet\Model\GuardedTransitionInterface as BaseTransitionInterface;

interface TransitionInterface extends BaseTransitionInterface
{
    public function setId(int $id): void;

    public function getLabel(): string;

    public function setLabel(string $label): void;

    public function getActions(): Collection;

    public function setActions(iterable $actions): void;

    public function setPetrinet(PetrinetInterface $petrinet): void;

    public function getPetrinet(): PetrinetInterface;
}
