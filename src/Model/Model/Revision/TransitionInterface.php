<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model\Revision;

interface TransitionInterface
{
    public function getLabel(): string;

    public function setLabel(string $label): void;

    public function getGuard(): ?string;

    public function setGuard(?string $guard): void;

    public function getExpression(): ?string;

    public function setExpression(?string $expression): void;

    public function getCommands(): array;

    public function setCommands(array $commands): void;

    public function addCommand(CommandInterface $command): void;

    public function getFromPlaces(): array;

    public function setFromPlaces(array $fromPlaces): void;

    public function addFromPlace(int $fromPlace): void;

    public function getToPlaces(): array;

    public function setToPlaces(array $toPlaces): void;

    public function addToPlace(int $toPlace): void;

    public function toArray(): array;

    public function isStart(): bool;
}
