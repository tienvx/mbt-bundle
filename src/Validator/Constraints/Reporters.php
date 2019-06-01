<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Reporters extends Constraint
{
    public $message = '"{{ string }}" is not a valid or supported reporter.';
}
