<?php

namespace Tienvx\Bundle\MbtBundle\Factory\Model;

use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

class CommandFactory
{
    public static function create(string $command, string $target, ?string $value = null): CommandInterface
    {
        $object = new Command();
        $object->setCommand($command);
        $object->setTarget($target);
        $object->setValue($value);

        return $object;
    }
}
