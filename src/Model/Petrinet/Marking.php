<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use SingleColorPetrinet\Model\ColorfulMarking as BaseMarking;

class Marking extends BaseMarking implements MarkingInterface
{
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function __clone()
    {
        $placeMarkings = [];
        foreach ($this->placeMarkings->toArray() as $placeMarking) {
            $placeMarkings[] = clone $placeMarking;
        }
        $this->setPlaceMarkings($placeMarkings);
        $this->color = clone $this->color;
    }
}
