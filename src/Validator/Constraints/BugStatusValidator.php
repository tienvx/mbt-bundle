<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;

/**
 * @Annotation
 */
class BugStatusValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof BugStatus) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\BugStatus');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!in_array($value, [BugWorkflow::NEW, BugWorkflow::REDUCING, BugWorkflow::REDUCED/*, BugWorkflow::REPORTING, BugWorkflow::REPORTED*/])) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
