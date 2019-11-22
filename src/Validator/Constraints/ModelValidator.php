<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;
use Tienvx\Bundle\MbtBundle\Entity\Model as ModelEntity;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;

/**
 * @Annotation
 */
class ModelValidator extends ConstraintValidator
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
        if (!$constraint instanceof Model) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Model');
        }

        if (!($value instanceof ModelEntity)) {
            throw new UnexpectedValueException($value, ModelEntity::class);
        }

        try {
            $this->workflowHelper->get($value->getName());
        } catch (InvalidArgumentException $exception) {
            $this->context->buildViolation($constraint->getMessage())
                ->setParameter('{{ string }}', $value->getName())
                ->addViolation();
        }
    }
}
