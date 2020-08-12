<?php

namespace Tienvx\Bundle\MbtBundle\Plugin;

interface PluginManagerInterface
{
    public function get(string $name): PluginInterface;

    public function has(string $name): bool;

    public function all(): array;
}
