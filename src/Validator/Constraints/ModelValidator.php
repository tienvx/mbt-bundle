<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

/**
 * @Annotation
 */
class ModelValidator extends ConstraintValidator
{
    /**
     * @var Registry
     */
    protected $workflowRegistry;

    /**
     * @var SubjectManager
     */
    protected $subjectManager;

    public function __construct(
        Registry $workflowRegistry,
        SubjectManager $subjectManager
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->subjectManager = $subjectManager;
    }

    /**
     * @param mixed $value
     * @param Constraint $constraint
     * @throws \Exception
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Model) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Model');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $subject = $this->subjectManager->createSubjectForModel($value);
        if (!$this->workflowRegistry->get($subject, $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
