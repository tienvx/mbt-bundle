<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Exception;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Entity\Model as ModelEntity;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;

/**
 * @Annotation
 */
class ModelValidator extends ConstraintValidator
{
    /**
     * @var Registry
     */
    protected $workflowRegistry;

    public function setWorkflowRegistry(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     *
     * @throws Exception
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Model) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Model');
        }

        if (!($value instanceof ModelEntity)) {
            throw new UnexpectedValueException($value, ModelEntity::class);
        }

        try {
            WorkflowHelper::get($this->workflowRegistry, $value->getName());
        } catch (InvalidArgumentException $exception) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value->getName())
                ->addViolation();
        }
    }
}
