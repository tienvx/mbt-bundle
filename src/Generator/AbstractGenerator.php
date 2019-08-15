<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

abstract class AbstractGenerator implements GeneratorInterface
{
    public static function support(): bool
    {
        return true;
    }
}
