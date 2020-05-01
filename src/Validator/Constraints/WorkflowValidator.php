<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;

/**
 * @Annotation
 */
class WorkflowValidator extends ConstraintValidator
{
    /**
     * @var WorkflowHelper
     */
    protected $workflowHelper;

    public function __construct(WorkflowHelper $workflowHelper)
    {
        $this->workflowHelper = $workflowHelper;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Workflow) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Workflow');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedValueException($value, 'string');
        }

        $this->validateValue($value, $constraint);
    }

    protected function validateValue(string $value, Constraint $constraint): void
    {
        if (!$this->workflowHelper->has($value)) {
            $this->context->buildViolation($constraint->getMessage())
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
