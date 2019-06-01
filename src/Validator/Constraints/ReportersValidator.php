<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Tienvx\Bundle\MbtBundle\Reporter\ReporterManager;

/**
 * @Annotation
 */
class ReportersValidator extends ConstraintValidator
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
        if (!$constraint instanceof Reporters) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Reporters');
        }

        if (!is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        if (empty($value)) {
            return;
        }

        foreach ($value as $reporter) {
            if (!$this->reporterManager->hasReporter($reporter)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ string }}', $reporter)
                    ->addViolation();
            }
        }
    }
}
