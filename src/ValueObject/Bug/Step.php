<?php

namespace Tienvx\Bundle\MbtBundle\ValueObject\Bug;

use SingleColorPetrinet\Model\ColorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Bug\Step as StepModel;

class Step extends StepModel
{
    /**
     * @Assert\All({
     *     @Assert\Type("integer")
     * })
     * @Assert\Count(min=1, minMessage="mbt.bug.missing_places_in_step")
     */
    protected array $places;

    /**
     * @Assert\Type("\SingleColorPetrinet\Model\Color")
     */
    protected ColorInterface $color;

    /**
     * @Assert\Type("integer")
     */
    protected int $transition;
}
