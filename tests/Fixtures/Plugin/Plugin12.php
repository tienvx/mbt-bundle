<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Fixtures\Plugin;

use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;

class Plugin12 implements PluginInterface
{
    public static function getManager(): string
    {
        return Manager1::class;
    }

    public static function getName(): string
    {
        return 'plugin12';
    }

    public static function isSupported(): bool
    {
        return false;
    }
}
