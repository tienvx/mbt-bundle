<?php

namespace Tienvx\Bundle\MbtBundle\ValueObject\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Model\Transition as TransitionModel;

class Transition extends TransitionModel
{
    /**
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    protected string $label;

    /**
     * @Assert\Type("string")
     */
    protected ?string $guard = null;

    /**
     * @Assert\All({
     *     @Assert\Type("\Tienvx\Bundle\MbtBundle\ValueObject\Model\Command"),
     *     @Assert\Valid
     * })
     */
    protected array $actions = [];

    /**
     * @Assert\All({
     *     @Assert\Type("integer")
     * })
     */
    protected array $fromPlaces = [];

    /**
     * @Assert\All({
     *     @Assert\Type("\Tienvx\Bundle\MbtBundle\ValueObject\Model\ToPlace"),
     *     @Assert\Valid
     * })
     */
    protected array $toPlaces = [];
}
