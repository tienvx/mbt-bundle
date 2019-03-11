<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Exception;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Tienvx\Bundle\MbtBundle\Graph\Path as GraphPath;

/**
 * @Annotation
 */
class PathValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Path) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Path');
        }

        if (!is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        if (empty($value)) {
            return;
        }

        try {
            GraphPath::unserialize($value);
        } catch (Exception $exception) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
