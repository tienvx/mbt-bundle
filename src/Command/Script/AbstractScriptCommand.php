<?php

namespace Tienvx\Bundle\MbtBundle\Command\Script;

use Tienvx\Bundle\MbtBundle\Command\AbstractCommand;

abstract class AbstractScriptCommand extends AbstractCommand
{
    public static function getGroup(): string
    {
        return 'script';
    }

    public static function validateTarget(?string $target): bool
    {
        return !empty($target);
    }
}
