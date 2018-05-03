<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Tienvx\Bundle\MbtBundle\Service\ReporterManager;

/**
 * @Annotation
 */
class ReporterValidator extends ConstraintValidator
{
    /**
     * @var ReporterManager
     */
    protected $reporterManager;

    public function __construct(ReporterManager $reporterManager)
    {
        $this->reporterManager = $reporterManager;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Reporter) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Reporter');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (!$this->reporterManager->hasReporter($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
