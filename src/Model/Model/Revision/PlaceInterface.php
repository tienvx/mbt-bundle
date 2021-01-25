<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model\Revision;

interface PlaceInterface
{
    public function getLabel(): string;

    public function setLabel(string $label): void;

    public function getCommands(): array;

    public function setCommands(array $commands): void;

    public function addCommand(CommandInterface $command): void;

    public function toArray(): array;
}
