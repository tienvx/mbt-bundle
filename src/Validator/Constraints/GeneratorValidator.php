<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;

/**
 * @Annotation
 */
class GeneratorValidator extends ConstraintValidator
{
    /**
     * @var GeneratorManager
     */
    protected $generatorManager;

    public function __construct(GeneratorManager $generatorManager)
    {
        $this->generatorManager = $generatorManager;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Generator) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Generator');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedValueException($value, 'string');
        }

        $this->validateValue($value, $constraint);
    }

    protected function validateValue(string $value, Constraint $constraint): void
    {
        if (!$this->generatorManager->has($value)) {
            $this->context->buildViolation($constraint->getMessage())
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
