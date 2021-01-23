<?php

namespace Tienvx\Bundle\MbtBundle\ValueObject\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Model\Transition as TransitionModel;

class Transition extends TransitionModel
{
    /**
     * @Assert\NotBlank
     */
    protected string $label = '';

    /**
     * @Assert\ExpressionLanguageSyntax
     */
    protected ?string $guard = null;

    /**
     * @Assert\All({
     *     @Assert\Type("\Tienvx\Bundle\MbtBundle\ValueObject\Model\Command")
     * })
     * @Assert\Valid
     */
    protected array $commands = [];

    /**
     * @Assert\All({
     *     @Assert\Type("integer")
     * })
     */
    protected array $fromPlaces = [];

    /**
     * @Assert\All({
     *     @Assert\Type("integer")
     * })
     * @Assert\Count(min=1, minMessage="mbt.model.missing_to_places")
     */
    protected array $toPlaces = [];
}
