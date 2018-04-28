<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;

/**
 * @Annotation
 */
class ModelValidator extends ConstraintValidator
{
    /**
     * @var ModelRegistry
     */
    protected $modelRegistry;

    public function __construct($options = null, ModelRegistry $modelRegistry)
    {
        $this->modelRegistry = $modelRegistry;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$this->modelRegistry->hasModel($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
