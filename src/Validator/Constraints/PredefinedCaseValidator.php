<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\PredefinedCase\PredefinedCaseManager;

/**
 * @Annotation
 */
class PredefinedCaseValidator extends ConstraintValidator
{
    /**
     * @var PredefinedCaseManager
     */
    protected $predefinedCaseManager;

    public function __construct(PredefinedCaseManager $predefinedCaseManager)
    {
        $this->predefinedCaseManager = $predefinedCaseManager;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PredefinedCase) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\PredefinedCase');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!$this->predefinedCaseManager->has($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
