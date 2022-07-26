<?php

namespace Tienvx\Bundle\MbtBundle\Model;

class Values implements ValuesInterface
{
    public function __construct(protected array $values = [])
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
