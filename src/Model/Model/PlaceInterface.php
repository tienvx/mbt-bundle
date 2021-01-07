<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model;

interface PlaceInterface
{
    public function getLabel(): string;

    public function setLabel(string $label): void;

    public function getStart(): bool;

    public function setStart(bool $start): void;

    public function getAssertions(): array;

    public function setAssertions(array $assertions): void;

    public function addAssertion(CommandInterface $assertion): void;

    public function isSame(self $place): bool;
}
