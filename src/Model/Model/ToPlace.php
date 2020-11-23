<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model;

class ToPlace implements ToPlaceInterface
{
    protected int $place;
    protected ?string $expression = null;

    public function getPlace(): int
    {
        return $this->place;
    }

    public function setPlace(int $place): void
    {
        $this->place = $place;
    }

    public function getExpression(): ?string
    {
        return $this->expression;
    }

    public function setExpression(?string $expression): void
    {
        $this->expression = $expression;
    }
}
