<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Doctrine\Common\Collections\Collection;
use Petrinet\Model\PlaceInterface as BasePlaceInterface;

interface PlaceInterface extends BasePlaceInterface
{
    public function setId(int $id): void;

    public function getLabel(): string;

    public function setLabel(string $label): void;

    public function getInit(): bool;

    public function setInit(bool $init): void;

    public function getAssertions(): Collection;

    public function setAssertions(iterable $assertions): void;

    public function setPetrinet(PetrinetInterface $petrinet): void;

    public function getPetrinet(): PetrinetInterface;
}
