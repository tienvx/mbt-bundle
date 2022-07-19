<?php

namespace Tienvx\Bundle\MbtBundle\Plugin;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: [PluginInterface::TAG], lazy: true)]
interface PluginInterface
{
    public const TAG = 'mbt_bundle.plugin';

    public static function getManager(): string;

    public static function getName(): string;

    public static function isSupported(): bool;
}
