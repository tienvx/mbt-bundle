<?php

namespace Tienvx\Bundle\MbtBundle\ValueObject\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\Place as PlaceModel;

class Place extends PlaceModel
{
    /**
     * @Assert\NotBlank
     */
    protected string $label = '';

    /**
     * @Assert\All({
     *     @Assert\Type("\Tienvx\Bundle\MbtBundle\ValueObject\Model\Command")
     * })
     * @Assert\Valid
     */
    protected array $commands = [];
}
