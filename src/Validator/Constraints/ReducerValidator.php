<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Entity\Reducer as ReducerEntity;
use Tienvx\Bundle\MbtBundle\PathReducer\PathReducerManager;

/**
 * @Annotation
 */
class ReducerValidator extends ConstraintValidator
{
    /**
     * @var PathReducerManager
     */
    protected $pathReducerManager;

    public function __construct(PathReducerManager $pathReducerManager)
    {
        $this->pathReducerManager = $pathReducerManager;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Reducer) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Reducer');
        }

        if (!($value instanceof ReducerEntity)) {
            throw new UnexpectedValueException($value, ReducerEntity::class);
        }

        if (!$this->pathReducerManager->hasPathReducer($value->getName())) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value->getName())
                ->addViolation();
        }
    }
}
