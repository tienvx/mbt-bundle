<?php

namespace Tienvx\Bundle\MbtBundle\Service\Petrinet;

use Petrinet\Model\MarkingInterface;
use SingleColorPetrinet\Model\ColorfulMarkingInterface;
use SingleColorPetrinet\Model\ColorInterface;
use SingleColorPetrinet\Model\PetrinetInterface;

interface MarkingHelperInterface
{
    /**
     * Count tokens by place in marking.
     */
    public function getPlaces(MarkingInterface $marking): array;

    /**
     * Re-create marking from tokens count by each place.
     */
    public function getMarking(
        PetrinetInterface $petrinet,
        array $places,
        ?ColorInterface $color = null
    ): ColorfulMarkingInterface;
}
