<?php

namespace Tienvx\Bundle\MbtBundle\Command\Mouse;

use Tienvx\Bundle\MbtBundle\Command\AbstractCommand;

abstract class AbstractMouseCommand extends AbstractCommand
{
    public static function getGroup(): string
    {
        return 'mouse';
    }
}
