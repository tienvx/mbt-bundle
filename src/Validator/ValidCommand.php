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

    public string $message = 'The command is not valid.';
}
