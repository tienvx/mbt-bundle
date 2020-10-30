<?php

namespace Tienvx\Bundle\MbtBundle\Model\Selenium;

interface CommandInterface
{
    public function getCommand(): string;

    public function setCommand(string $command): void;

    public function getTarget(): string;

    public function setTarget(string $target): void;

    public function getValue(): string;

    public function setValue(string $value): void;
}
