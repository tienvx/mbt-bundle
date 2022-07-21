<?php

namespace Tienvx\Bundle\MbtBundle\Command\Alert;

use Tienvx\Bundle\MbtBundle\Command\AbstractCommand;

abstract class AbstractAlertCommand extends AbstractCommand
{
    public static function getGroup(): string
    {
        return 'alert';
    }

    public static function validateTarget(?string $target): bool
    {
        return true;
    }

    public static function getTargetHelper(): string
    {
        return '';
    }
}
