<?php

namespace Tienvx\Bundle\MbtBundle\Model;

class Values implements ValuesInterface
{
    protected array $values = [];

    public function __construct(array $values = [])
    {
        $this->values = [];

        foreach ($values as $key => $value) {
            $this->setValue($key, $value);
        }
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function setValue(string $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    public function getValue(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }
}
