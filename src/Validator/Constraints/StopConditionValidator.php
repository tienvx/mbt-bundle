<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Tienvx\Bundle\MbtBundle\StopCondition\StopConditionManager;

/**
 * @Annotation
 */
class StopConditionValidator extends ConstraintValidator
{
    /**
     * @var StopConditionManager
     */
    protected $stopConditionManager;

    public function __construct(StopConditionManager $stopConditionManager)
    {
        $this->stopConditionManager = $stopConditionManager;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof StopCondition) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\StopCondition');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (!$this->stopConditionManager->hasStopCondition($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
