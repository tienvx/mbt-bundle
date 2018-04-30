<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Tienvx\Bundle\MbtBundle\Service\GeneratorManager;

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

    public function validate($value, Constraint $constraint)
    {
        if (!$this->generatorManager->hasGenerator($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
