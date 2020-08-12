<?php

namespace Tienvx\Bundle\MbtBundle\Model\Petrinet;

use Petrinet\Model\InputArcInterface as BaseInputArcInterface;

interface InputArcInterface extends BaseInputArcInterface
{
    public function setId(int $id): void;
}
