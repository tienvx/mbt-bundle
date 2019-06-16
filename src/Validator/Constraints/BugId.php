<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class BugId extends Constraint
{
    public $message = '"{{ number }}" is not a valid bug id.';
}
