<?php

namespace Tienvx\Bundle\MbtBundle\ValueObject\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\AssignmentsEvaluatorBundle\Validator\AssignmentsSyntax;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition as TransitionModel;

class Transition extends TransitionModel
{
    #[Assert\NotBlank]
    protected string $label = '';

    #[Assert\AtLeastOneOf([
        new Assert\IsNull(),
        new Assert\ExpressionSyntax(),
    ])]
    protected ?string $guard = null;

    #[Assert\AtLeastOneOf([
        new Assert\IsNull(),
        new AssignmentsSyntax(),
    ])]
    protected ?string $expression = null;

    #[Assert\All([
        new Assert\Type(Command::class),
    ])]
    #[Assert\Valid]
    protected array $commands = [];

    #[Assert\All([
        new Assert\Type('integer'),
    ])]
    protected array $fromPlaces = [];

    #[Assert\All([
        new Assert\Type('integer'),
    ])]
    #[Assert\Count(min: 1, minMessage: 'mbt.model.missing_to_places')]
    protected array $toPlaces = [];
}
