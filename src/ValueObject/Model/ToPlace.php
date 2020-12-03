<?php

namespace Tienvx\Bundle\MbtBundle\ValueObject\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Model\ToPlace as ToPlaceModel;

class ToPlace extends ToPlaceModel
{
    /**
     * @Assert\Type("integer")
     */
    protected int $place;

    /**
     * @Assert\Type("string")
     */
    protected ?string $expression = null;
}
