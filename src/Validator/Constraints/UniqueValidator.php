<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @Annotation
 */
class UniqueValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Unique) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Unique');
        }

        if (empty($value)) {
            return;
        }

        if (!is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        if (count($value) !== count(array_unique($value))) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $this->formatValue($value))
                ->addViolation();
        }
    }
}
