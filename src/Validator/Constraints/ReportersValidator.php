<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Entity\Reporter as ReporterEntity;
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

    public function validate($values, Constraint $constraint): void
    {
        if (!$constraint instanceof Reporters) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Reporters');
        }

        if (!is_array($values)) {
            throw new UnexpectedValueException($values, 'array');
        }

        if (0 === count($values)) {
            return;
        }

        $this->validateValue($values, $constraint);
    }

    protected function validateValue($values, Constraint $constraint): void
    {
        foreach ($values as $value) {
            if (!($value instanceof ReporterEntity)) {
                throw new UnexpectedValueException($value, ReporterEntity::class);
            }

            if (!$this->reporterManager->has($value->getName())) {
                $this->context->buildViolation($constraint->getMessage())
                    ->setParameter('{{ string }}', $value->getName())
                    ->addViolation();
            }
        }
    }
}
