<?php

namespace Tienvx\Bundle\MbtBundle\Command\Keyboard;

use Tienvx\Bundle\MbtBundle\Command\AbstractCommand;

abstract class AbstractKeyboardCommand extends AbstractCommand
{
    public static function getGroup(): string
    {
        return 'keyboard';
    }

    public function validateValue(?string $value): bool
    {
        return !empty($value);
    }
}
