<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
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

    public function validate($values, Constraint $constraint)
    {
        if (!$constraint instanceof Reporters) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Reporters');
        }

        if (!is_array($values)) {
            throw new UnexpectedTypeException($values, 'array');
        }

        if (empty($values)) {
            return;
        }

        foreach ($values as $value) {
            if (!($value instanceof ReporterEntity)) {
                throw new UnexpectedTypeException($value, ReporterEntity::class);
            }

            if (!$this->reporterManager->hasReporter($value->getName())) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ string }}', $value->getName())
                    ->addViolation();
            }
        }
    }
}
