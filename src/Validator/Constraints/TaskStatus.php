<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TaskStatus extends Constraint
{
    /**
     * @var string
     */
    protected $message = '"{{ string }}" is not a valid task status.';

    public function getMessage(): string
    {
        return $this->message;
    }
}
