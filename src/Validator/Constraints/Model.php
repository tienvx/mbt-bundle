<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Model extends Constraint
{
    /**
     * @var string
     */
    protected $message = '"{{ string }}" is not a valid model.';

    public function getMessage(): string
    {
        return $this->message;
    }
}
