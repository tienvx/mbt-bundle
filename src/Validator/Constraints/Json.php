<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Json extends Constraint
{
    public $message = '"{{ string }}" is not a valid json string.';
}
