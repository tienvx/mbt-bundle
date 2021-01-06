<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Tienvx\Bundle\MbtBundle\Plugin\PluginManagerInterface;

interface GeneratorManagerInterface extends PluginManagerInterface
{
    public function getGenerator(string $name): GeneratorInterface;
}
