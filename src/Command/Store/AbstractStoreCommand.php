<?php

namespace Tienvx\Bundle\MbtBundle\Command\Store;

use Tienvx\Bundle\MbtBundle\Command\AbstractCommand;

abstract class AbstractStoreCommand extends AbstractCommand
{
    public static function getGroup(): string
    {
        return 'store';
    }
}
