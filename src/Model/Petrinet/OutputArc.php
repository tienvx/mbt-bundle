<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use SingleColorPetrinet\Model\ExpressionalOutputArc as BaseOutputArc;

class OutputArc extends BaseOutputArc implements OutputArcInterface
{
    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
