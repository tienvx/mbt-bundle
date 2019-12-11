<?php

namespace Tienvx\Bundle\MbtBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Entity\Model as ModelEntity;
use Tienvx\Bundle\MbtBundle\Helper\ModelHelper;

/**
 * @Annotation
 */
class ModelValidator extends ConstraintValidator
{
    /**
     * @var ModelHelper
     */
    protected $modelHelper;

    public function __construct(ModelHelper $modelHelper)
    {
        $this->modelHelper = $modelHelper;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Model) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Model');
        }

        if (!($value instanceof ModelEntity)) {
            throw new UnexpectedValueException($value, ModelEntity::class);
        }

        $this->validateValue($value, $constraint);
    }

    protected function validateValue($value, Constraint $constraint): void
    {
        if (!$this->modelHelper->has($value->getName())) {
            $this->context->buildViolation($constraint->getMessage())
                ->setParameter('{{ string }}', $value->getName())
                ->addViolation();
        }
    }
}
