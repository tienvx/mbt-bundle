<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Reporter extends Constraint
{
    public $message = '"{{ string }}" is not a valid reporter.';
}
