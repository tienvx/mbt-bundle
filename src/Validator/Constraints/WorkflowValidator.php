<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Model\WorkflowInterface;

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

        if (!($value instanceof WorkflowInterface)) {
            throw new UnexpectedValueException($value, WorkflowInterface::class);
        }

        $this->validateValue($value, $constraint);
    }

    protected function validateValue(WorkflowInterface $value, Constraint $constraint): void
    {
        if (!$this->workflowHelper->has($value->getName())) {
            $this->context->buildViolation($constraint->getMessage())
                ->setParameter('{{ string }}', $value->getName())
                ->addViolation();
        }
    }
}
