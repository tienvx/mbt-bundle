<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Entity\Reducer as ReducerEntity;
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

        if (!($value instanceof ReducerEntity)) {
            throw new UnexpectedValueException($value, ReducerEntity::class);
        }

        if (!$this->reducerManager->has($value->getName())) {
            $this->context->buildViolation($constraint->getMessage())
                ->setParameter('{{ string }}', $value->getName())
                ->addViolation();
        }
    }
}
