<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model;

class Place implements PlaceInterface
{
    use HasCommands;

    protected string $label = '';

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function isSame(PlaceInterface $place): bool
    {
        return $this->isSameCommands($place->getCommands());
    }
}
