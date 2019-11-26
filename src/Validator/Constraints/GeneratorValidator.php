<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Entity\Generator as GeneratorEntity;
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

        if (!($value instanceof GeneratorEntity)) {
            throw new UnexpectedValueException($value, GeneratorEntity::class);
        }

        $this->validateValue($value, $constraint);
    }

    protected function validateValue($value, Constraint $constraint): void
    {
        if (!$this->generatorManager->has($value->getName())) {
            $this->context->buildViolation($constraint->getMessage())
                ->setParameter('{{ string }}', $value->getName())
                ->addViolation();
        }
    }
}
