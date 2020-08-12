<?php

namespace Tienvx\Bundle\MbtBundle\Channel;

abstract class AbstractChannel implements ChannelInterface
{
    public static function getManager(): string
    {
        return ChannelManager::class;
    }
}
