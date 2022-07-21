<?php

namespace Tienvx\Bundle\MbtBundle\Command\Assert;

use Tienvx\Bundle\MbtBundle\Command\AbstractCommand;

abstract class AbstractAssertCommand extends AbstractCommand
{
    public static function getGroup(): string
    {
        return 'assert';
    }
}
