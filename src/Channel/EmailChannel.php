<?php

namespace Tienvx\Bundle\MbtBundle\Channel;

class EmailChannel extends AbstractChannel
{
    public static function getName(): string
    {
        return 'email';
    }

    public static function isSupported(): bool
    {
        return true;
    }
}
