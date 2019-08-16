<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class BugStatus extends Constraint
{
    public $message = '"{{ string }}" is not a valid bug status.';
}
