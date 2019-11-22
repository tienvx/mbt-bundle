<?php

namespace Tienvx\Bundle\MbtBundle\Plugin;

interface PluginInterface
{
    public static function getName(): string;

    public static function support(): bool;
}
