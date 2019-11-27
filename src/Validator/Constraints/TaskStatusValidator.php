<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Workflow\TaskWorkflow;

/**
 * @Annotation
 */
class TaskStatusValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof TaskStatus) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\TaskStatus');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedValueException($value, 'string');
        }

        $this->validateValue($value, $constraint);
    }

    protected function validateValue($value, Constraint $constraint): void
    {
        if (!in_array($value, [TaskWorkflow::NOT_STARTED, TaskWorkflow::IN_PROGRESS, TaskWorkflow::COMPLETED])) {
            $this->context->buildViolation($constraint->getMessage())
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
