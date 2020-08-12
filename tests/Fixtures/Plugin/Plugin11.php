<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Fixtures\Plugin;

use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;

class Plugin11 implements PluginInterface
{
    public static function getManager(): string
    {
        return Manager1::class;
    }

    public static function getName(): string
    {
        return 'plugin11';
    }

    public static function isSupported(): bool
    {
        return true;
    }
}
