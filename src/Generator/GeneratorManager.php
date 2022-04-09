<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Tienvx\Bundle\MbtBundle\Plugin\PluginManager;

class GeneratorManager extends PluginManager implements GeneratorManagerInterface
{
    public function getGenerator(string $name): GeneratorInterface
    {
        return parent::get($name);
    }

    protected function getPluginInterface(): string
    {
        return GeneratorInterface::class;
    }

    protected function getInvalidPluginExceptionMessage(string $name): string
    {
        return sprintf('Generator "%s" does not exist.', $name);
    }
}
