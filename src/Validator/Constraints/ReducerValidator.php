<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
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

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (!$this->pathReducerManager->hasPathReducer($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
