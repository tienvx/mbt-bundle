<?php

namespace Tienvx\Bundle\MbtBundle\Plugin;

interface PluginInterface
{
    public const TAG = 'mbt_bundle.plugin';

    public static function getManager(): string;

    public static function getName(): string;

    public static function isSupported(): bool;
}
