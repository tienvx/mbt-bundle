<?php

namespace Tienvx\Bundle\MbtBundle\Service\AStar;

use JMGQ\AStar\DomainLogicInterface;
use SingleColorPetrinet\Model\PetrinetInterface;

interface PetrinetDomainLogicInterface extends DomainLogicInterface
{
    public function setPetrinet(?PetrinetInterface $petrinet): void;
}
