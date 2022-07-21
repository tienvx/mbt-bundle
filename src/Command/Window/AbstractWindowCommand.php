<?php

namespace Tienvx\Bundle\MbtBundle\Command\Window;

use Tienvx\Bundle\MbtBundle\Command\AbstractCommand;

abstract class AbstractWindowCommand extends AbstractCommand
{
    public static function getGroup(): string
    {
        return 'window';
    }

    public static function isValueRequired(): bool
    {
        return false;
    }
}
