<?php

namespace Tienvx\Bundle\MbtBundle\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ValidCommand extends Constraint
{
    public const IS_COMMAND_INVALID_ERROR = 'ba5fd751-cbdf-45ab-a1e7-37045d5ef44b';

    protected static $errorNames = [
        self::IS_COMMAND_INVALID_ERROR => 'IS_COMMAND_INVALID_ERROR',
    ];

    public string $invalidCommandMessage = 'mbt.model.command.invalid_command';
    public string $targetRequiredMessage = 'mbt.model.command.required_target';
    public string $targetInvalidMessage = 'mbt.model.command.invalid_target';
    public string $valueRequiredMessage = 'mbt.model.command.required_value';
    public string $valueInvalidMessage = 'mbt.model.command.invalid_value';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
