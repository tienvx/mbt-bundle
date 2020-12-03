<?php

namespace Tienvx\Bundle\MbtBundle\ValueObject\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Model\ToPlace as ToPlaceModel;

class ToPlace extends ToPlaceModel
{
    protected int $place;
    protected ?string $expression = null;
}
