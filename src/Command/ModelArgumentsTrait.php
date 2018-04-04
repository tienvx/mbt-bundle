<?php

namespace Tienvx\Bundle\MbtBundle\Command;

trait ModelArgumentsTrait
{
    private function parseModelArguments(string $arguments = null): array
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