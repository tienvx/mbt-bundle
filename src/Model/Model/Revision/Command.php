<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model\Revision;

class Command implements CommandInterface
{
    protected string $command;
    protected ?string $target = null;
    protected ?string $value = null;

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(array $data)
    {
        $this->command = $data['command'];
        $this->target = $data['target'];
        $this->value = $data['value'];
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(?string $target): void
    {
        $this->target = $target;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    public function toArray(): array
    {
        return [
            'command' => $this->command,
            'target' => $this->target,
            'value' => $this->value,
        ];
    }
}
