<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Doctrine\Common\Collections\Collection;
use SingleColorPetrinet\Model\ColorfulMarkingInterface as BaseMarkingInterface;

interface MarkingInterface extends BaseMarkingInterface
{
    public function setId(int $id): void;

    /**
     * Gets the place markings.
     *
     * @return Collection
     */
    public function getPlaceMarkings();
}
