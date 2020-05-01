<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;

/**
 * @Annotation
 */
class ReducerValidator extends ConstraintValidator
{
    /**
     * @var ReducerManager
     */
    protected $reducerManager;

    public function __construct(ReducerManager $reducerManager)
    {
        $this->reducerManager = $reducerManager;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Reducer) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Reducer');
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
        if (!$this->reducerManager->has($value)) {
            $this->context->buildViolation($constraint->getMessage())
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
