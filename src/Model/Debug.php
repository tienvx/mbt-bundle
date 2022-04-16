<?php

namespace Tienvx\Bundle\MbtBundle\Model;

abstract class Debug implements DebugInterface
{
    protected bool $debug = false;

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }
}
