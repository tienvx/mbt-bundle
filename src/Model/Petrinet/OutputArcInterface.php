<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use SingleColorPetrinet\Model\ExpressionalOutputArcInterface as BaseOutputArcInterface;

interface OutputArcInterface extends BaseOutputArcInterface
{
    public function setId(int $id): void;
}
