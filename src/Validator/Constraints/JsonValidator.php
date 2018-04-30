<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class JsonValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!is_string($value) || !json_decode($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $this->formatValue($value))
                ->addViolation();
        }
    }
}
