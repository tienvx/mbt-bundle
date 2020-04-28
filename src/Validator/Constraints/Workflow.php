<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Workflow extends Constraint
{
    /**
     * @var string
     */
    protected $message = '"{{ string }}" is not a valid workflow.';

    public function getMessage(): string
    {
        return $this->message;
    }
}
