<?php

namespace Tienvx\Bundle\MbtBundle\ValueObject\Model;

use Tienvx\Bundle\MbtBundle\Model\Model\ToPlace as ToPlaceModel;

class ToPlace extends ToPlaceModel
{
    protected int $place;
    protected ?string $expression = null;
}
