<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Tienvx\Bundle\MbtBundle\Service\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Service\PathReducerManager;

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
        if (!$this->pathReducerManager->hasPathReducer($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
