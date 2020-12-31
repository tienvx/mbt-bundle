<?php

namespace Tienvx\Bundle\MbtBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunnerManagerInterface;

class ValidCommandValidator extends ConstraintValidator
{
    protected CommandRunnerManagerInterface $commandRunnerManager;

    public function __construct(CommandRunnerManagerInterface $commandRunnerManager)
    {
        $this->commandRunnerManager = $commandRunnerManager;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ValidCommand) {
            throw new UnexpectedTypeException($constraint, ValidCommand::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (
            !in_array($value, $this->commandRunnerManager->getActions())
            && !in_array($value, $this->commandRunnerManager->getAssertions())
        ) {
            $this->context->buildViolation($constraint->message)
                ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
                ->addViolation();
        }
    }
}
