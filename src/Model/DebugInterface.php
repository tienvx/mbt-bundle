<?php

namespace Tienvx\Bundle\MbtBundle\Model;

interface DebugInterface
{
    public function isDebug(): bool;

    public function setDebug(bool $debug): void;

    public function getLogName(): string;

    public function getVideoName(): string;

    public function getTask(): TaskInterface;
}
