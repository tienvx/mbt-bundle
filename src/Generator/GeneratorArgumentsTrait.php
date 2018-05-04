<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

trait GeneratorArgumentsTrait
{
    private function parseGeneratorArguments(string $arguments = null): array
    {
        if (is_string($arguments)) {
            $arguments = json_decode($arguments, true);
        }
        else {
            $arguments = [];
        }
        return $arguments;
    }
}