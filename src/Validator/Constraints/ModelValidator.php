<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Exception;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

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
     * @param mixed $value
     * @param Constraint $constraint
     * @throws Exception
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$this->workflowRegistry instanceof Registry) {
            throw new Exception('Can not validate model: No workflows were defined');
        }

        if (!$constraint instanceof Model) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Model');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $subject = new class extends Subject {
        };
        if (!$this->workflowRegistry->get($subject, $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
