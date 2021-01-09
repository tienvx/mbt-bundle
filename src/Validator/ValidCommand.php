<?php

namespace Tienvx\Bundle\MbtBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidCommand extends Constraint
{
    public const IS_COMMAND_INVALID_ERROR = 'ba5fd751-cbdf-45ab-a1e7-37045d5ef44b';

    protected static $errorNames = [
        self::IS_COMMAND_INVALID_ERROR => 'IS_COMMAND_INVALID_ERROR',
    ];

    public string $commandMessage = 'The command is not valid.';
    public string $targetMessage = 'The target is not valid.';
    public string $valueMessage = 'The value is not valid.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
