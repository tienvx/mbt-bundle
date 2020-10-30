<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Petrinet\Model\PlaceMarkingInterface as BasePlaceMarkingInterface;

interface PlaceMarkingInterface extends BasePlaceMarkingInterface
{
    public function setId(int $id): void;
}
