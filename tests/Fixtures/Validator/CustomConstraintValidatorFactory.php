<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Fixtures\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Tienvx\Bundle\MbtBundle\Command\CommandPreprocessor;
use Tienvx\Bundle\MbtBundle\Command\CommandRunnerManager;
use Tienvx\Bundle\MbtBundle\Command\Runner\AlertCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\AssertionRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\KeyboardCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\ScriptCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\StoreCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\WaitCommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\WindowCommandRunner;
use Tienvx\Bundle\MbtBundle\Validator\ValidCommandValidator;

class CustomConstraintValidatorFactory extends ConstraintValidatorFactory
{
    public function getInstance(Constraint $constraint)
    {
        $className = $constraint->validatedBy();
        if (ValidCommandValidator::class === $className && !isset($this->validators[$className])) {
            $this->validators[$className] = new ValidCommandValidator(new CommandRunnerManager(
                [
                    new AlertCommandRunner(),
                    new AssertionRunner(),
                    new KeyboardCommandRunner(),
                    new MouseCommandRunner(),
                    new ScriptCommandRunner(),
                    new StoreCommandRunner(),
                    new WaitCommandRunner(),
                    new WindowCommandRunner(),
                ],
                new CommandPreprocessor()
            ));
        }

        return parent::getInstance($constraint);
    }
}
