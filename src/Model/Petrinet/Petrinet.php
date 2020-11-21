<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Petrinet\Model\Petrinet as BasePetrinet;

class Petrinet extends BasePetrinet implements PetrinetInterface
{
    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
