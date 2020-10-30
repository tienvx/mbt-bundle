<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Petrinet\Model\InputArc as BaseInputArc;

class InputArc extends BaseInputArc implements InputArcInterface
{
    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
