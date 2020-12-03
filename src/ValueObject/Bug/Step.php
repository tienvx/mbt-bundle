<?php

namespace Tienvx\Bundle\MbtBundle\ValueObject\Bug;

use SingleColorPetrinet\Model\ColorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Bug\Step as StepModel;

class Step extends StepModel
{
    protected array $places;

    /**
     * @Assert\Type("\SingleColorPetrinet\Model\Color")
     */
    protected ColorInterface $color;

    protected ?int $transition = null;
}
