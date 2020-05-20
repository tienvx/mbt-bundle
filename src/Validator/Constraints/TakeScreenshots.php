<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TakeScreenshots extends Constraint
{
    /**
     * @var string
     */
    protected $message = 'Taking screenshots feature is not enabled.';

    public function getMessage(): string
    {
        return $this->message;
    }
}
