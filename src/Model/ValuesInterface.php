<?php

namespace Tienvx\Bundle\MbtBundle\Model;

interface ValuesInterface
{
    public function getValues(): array;

    public function setValue(string $key, mixed $value): void;

    public function getValue(string $key): mixed;
}
