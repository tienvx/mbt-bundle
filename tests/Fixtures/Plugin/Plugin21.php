<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Fixtures\Plugin;

use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;

class Plugin21 implements PluginInterface
{
    public static function getManager(): string
    {
        return Manager2::class;
    }

    public static function getName(): string
    {
        return 'plugin21';
    }

    public static function isSupported(): bool
    {
        return true;
    }
}
