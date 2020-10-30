<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Petrinet\Model\PetrinetInterface as BasePetrinetInterface;

interface PetrinetInterface extends BasePetrinetInterface
{
    public function setId(int $id): void;

    public function getInitPlaceIds(): array;

    public function setInitPlaceIds(array $initPlaceIds): void;
}
