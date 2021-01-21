<?php

namespace Tienvx\Bundle\MbtBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Command\CommandRunnerManagerInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

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

        if (null === $value) {
            return;
        }

        if (!$value instanceof Command) {
            throw new UnexpectedValueException($value, Command::class);
        }

        if (!in_array($value->getCommand(), $this->commandRunnerManager->getAllCommands())) {
            $this->context->buildViolation($constraint->commandMessage)
                ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
                ->atPath('command')
                ->addViolation();
        }

        if (in_array($value->getCommand(), $this->commandRunnerManager->getCommandsRequireTarget())) {
            if (is_null($value->getTarget())) {
                $this->context->buildViolation($constraint->targetRequiredMessage)
                    ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
                    ->atPath('target')
                    ->addViolation();
            } elseif (!$this->commandRunnerManager->validateTarget($value)) {
                $this->context->buildViolation($constraint->targetInvalidMessage)
                    ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
                    ->atPath('target')
                    ->addViolation();
            }
        }

        if (
            in_array($value->getCommand(), $this->commandRunnerManager->getCommandsRequireValue())
            && is_null($value->getValue())
        ) {
            $this->context->buildViolation($constraint->valueRequiredMessage)
                ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
                ->atPath('value')
                ->addViolation();
        }
    }
}
