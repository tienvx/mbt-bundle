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
     * @Assert\ExpressionLanguageSyntax
     */
    protected ?string $expression = null;

    /**
     * @Assert\All({
     *     @Assert\Type("\Tienvx\Bundle\MbtBundle\ValueObject\Model\Command")
     * })
     * @Assert\Valid
     */
    protected array $actions = [];

    /**
     * @Assert\All({
     *     @Assert\Type("integer")
     * })
     * @Assert\Count(min=1, minMessage="This transition should connect at least 1 place to other places.")
     */
    protected array $fromPlaces = [];

    /**
     * @Assert\All({
     *     @Assert\Type("integer")
     * })
     * @Assert\Count(min=1, minMessage="This transition should connect some places to at least 1 place.")
     */
    protected array $toPlaces = [];
}
