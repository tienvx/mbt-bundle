<?php

namespace Tienvx\Bundle\MbtBundle\Factory\Model\Revision;

use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

class CommandFactory
{
    public static function create(string $command, ?string $target = null, ?string $value = null): CommandInterface
    {
        $object = new Command();
        $object->setCommand($command);
        $object->setTarget($target);
        $object->setValue($value);

        return $object;
    }

    public static function createFromArray(array $data): CommandInterface
    {
        return static::create($data['command'] ?? '', $data['target'] ?? null, $data['value'] ?? null);
    }
}
