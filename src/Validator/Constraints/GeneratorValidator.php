<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
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

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Generator) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Generator');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (!$this->generatorManager->hasGenerator($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
