<?php

namespace Tienvx\Bundle\MbtBundle\Command\Wait;

use Tienvx\Bundle\MbtBundle\Command\AbstractCommand;

abstract class AbstractWaitCommand extends AbstractCommand
{
    public static function getGroup(): string
    {
        return 'wait';
    }

    public static function isTargetRequired(): bool
    {
        return true;
    }

    public static function isValueRequired(): bool
    {
        return true;
    }

    public static function getValueHelper(): string
    {
        return 'Seconds';
    }

    public function validateValue(?string $value): bool
    {
        return is_numeric($value);
    }
}
