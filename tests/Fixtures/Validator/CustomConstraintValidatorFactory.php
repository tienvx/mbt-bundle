<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Fixtures\Validator;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandManager;
use Tienvx\Bundle\MbtBundle\Validator\ValidCommandValidator;

class CustomConstraintValidatorFactory extends ConstraintValidatorFactory
{
    public function getInstance(Constraint $constraint): ConstraintValidatorInterface
    {
        $className = $constraint->validatedBy();
        if (ValidCommandValidator::class === $className && !isset($this->validators[$className])) {
            $this->validators[$className] = new ValidCommandValidator(new CommandManager(new MockHttpClient()));
        }

        return parent::getInstance($constraint);
    }
}
