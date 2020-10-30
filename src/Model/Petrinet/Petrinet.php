<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Petrinet\Model\Petrinet as BasePetrinet;

class Petrinet extends BasePetrinet implements PetrinetInterface
{
    protected array $initPlaceIds = [];

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getInitPlaceIds(): array
    {
        return $this->initPlaceIds;
    }

    public function setInitPlaceIds(array $initPlaceIds): void
    {
        $this->initPlaceIds = $initPlaceIds;
    }
}
