<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class BugStatus extends Constraint
{
    /**
     * @var string
     */
    protected $message = '"{{ string }}" is not a valid bug status.';

    public function getMessage(): string
    {
        return $this->message;
    }
}
