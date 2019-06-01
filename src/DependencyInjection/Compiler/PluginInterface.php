<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

interface PluginInterface
{
    public static function getName(): string;

    public static function support(): bool;
}
