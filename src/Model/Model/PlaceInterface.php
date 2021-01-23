<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model;

interface PlaceInterface
{
    public function getLabel(): string;

    public function setLabel(string $label): void;

    public function getCommands(): array;

    public function setCommands(array $commands): void;

    public function addCommand(CommandInterface $command): void;

    public function isSame(self $place): bool;
}
