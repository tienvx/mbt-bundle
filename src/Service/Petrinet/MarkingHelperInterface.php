<?php

namespace Tienvx\Bundle\MbtBundle\Service\Petrinet;

use Petrinet\Model\MarkingInterface;
use Petrinet\Model\PetrinetInterface;
use SingleColorPetrinet\Model\ColorfulMarkingInterface;

interface MarkingHelperInterface
{
    /**
     * Count tokens by place in marking.
     */
    public function getPlaces(MarkingInterface $marking): array;

    /**
     * Re-create marking from tokens count by each place.
     */
    public function getMarking(PetrinetInterface $petrinet, array $places): ColorfulMarkingInterface;
}
