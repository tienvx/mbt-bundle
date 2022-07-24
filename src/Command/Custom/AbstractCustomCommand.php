<?php

namespace Tienvx\Bundle\MbtBundle\Command\Custom;

use Tienvx\Bundle\MbtBundle\Command\AbstractCommand;

abstract class AbstractCustomCommand extends AbstractCommand
{
    public static function getGroup(): string
    {
        return 'custom';
    }
}
