<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

abstract class AbstractGenerator implements GeneratorInterface
{
    public static function getManager(): string
    {
        return GeneratorManager::class;
    }

    public static function isSupported(): bool
    {
        return true;
    }
}
