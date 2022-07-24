<?php

namespace Tienvx\Bundle\MbtBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Command\CommandManagerInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

class ValidCommandValidator extends ConstraintValidator
{
    public function __construct(protected CommandManagerInterface $commandManager)
    {
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

        if (!$this->commandManager->hasCommand($value->getCommand())) {
            $this->context->buildViolation($constraint->invalidCommandMessage)
                ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
                ->atPath('command')
                ->addViolation();
        }

        if ($this->commandManager->isTargetMissing($value->getCommand(), $value->getTarget())) {
            $this->context->buildViolation($constraint->targetRequiredMessage)
                ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
                ->atPath('target')
                ->addViolation();
        } elseif ($this->commandManager->isTargetNotValid($value->getCommand(), $value->getTarget())) {
            $this->context->buildViolation($constraint->targetInvalidMessage)
                ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
                ->atPath('target')
                ->addViolation();
        }

        if ($this->commandManager->isValueMissing($value->getCommand(), $value->getValue())) {
            $this->context->buildViolation($constraint->valueRequiredMessage)
                ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
                ->atPath('value')
                ->addViolation();
        } elseif ($this->commandManager->isValueNotValid($value->getCommand(), $value->getValue())) {
            $this->context->buildViolation($constraint->valueInvalidMessage)
                ->setCode(ValidCommand::IS_COMMAND_INVALID_ERROR)
                ->atPath('value')
                ->addViolation();
        }
    }
}
