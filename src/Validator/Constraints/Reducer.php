<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Reducer extends Constraint
{
    /**
     * @var string
     */
    protected $message = '"{{ string }}" is not a valid or supported path reducer.';

    public function getMessage(): string
    {
        return $this->message;
    }
}
