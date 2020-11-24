<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model;

class Command implements CommandInterface
{
    protected string $command;
    protected string $target;
    protected ?string $value = null;

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): void
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

    public function isSame(CommandInterface $command): bool
    {
        return $this->getCommand() === $command->getCommand() ||
            $this->getTarget() === $command->getTarget() ||
            $this->getValue() === $command->getValue();
    }
}
