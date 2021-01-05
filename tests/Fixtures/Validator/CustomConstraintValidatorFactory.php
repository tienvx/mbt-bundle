<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Fixtures\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunnerManager;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\AlertCommandRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\AssertionRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\KeyboardCommandRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\WaitCommandRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\WindowCommandRunner;
use Tienvx\Bundle\MbtBundle\Validator\ValidCommandValidator;

class CustomConstraintValidatorFactory extends ConstraintValidatorFactory
{
    public function getInstance(Constraint $constraint)
    {
        $className = $constraint->validatedBy();
        if (ValidCommandValidator::class === $className && !isset($this->validators[$className])) {
            $this->validators[$className] = new ValidCommandValidator(new CommandRunnerManager([
                new AlertCommandRunner(),
                new AssertionRunner(),
                new KeyboardCommandRunner(),
                new MouseCommandRunner(),
                new WaitCommandRunner(),
                new WindowCommandRunner(),
            ]));
        }

        return parent::getInstance($constraint);
    }
}
