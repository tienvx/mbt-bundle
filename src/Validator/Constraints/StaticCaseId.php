<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class StaticCaseId extends Constraint
{
    public $message = '"{{ number }}" is not a valid static case id.';
}
