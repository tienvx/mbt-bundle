<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model;

class Place implements PlaceInterface
{
    use HasCommands;

    protected string $label = '';
    protected bool $start = false;

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getStart(): bool
    {
        return $this->start;
    }

    public function setStart(bool $start): void
    {
        $this->start = $start;
    }

    public function isSame(PlaceInterface $place): bool
    {
        return $this->getStart() === $place->getStart() && $this->isSameCommands($place->getCommands());
    }
}
