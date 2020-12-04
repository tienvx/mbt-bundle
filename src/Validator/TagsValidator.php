<?php

namespace Tienvx\Bundle\MbtBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class TagsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Tags) {
            throw new UnexpectedTypeException($constraint, Tags::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $tags = explode(',', $value);
        if (count($tags) !== count(array_unique($tags)) || count($tags) !== count(array_filter($tags))) {
            $this->context->buildViolation($constraint->message)
                ->setCode(Tags::IS_TAGS_INVALID_ERROR)
                ->addViolation();
        }
    }
}
