<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TransitionReducerWorkflowType extends Constraint
{
    /**
     * @var string
     */
    protected $message = 'Transition reducer is for workflow type only. "{{ workflow }}" has type "{{ type }}".';

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
