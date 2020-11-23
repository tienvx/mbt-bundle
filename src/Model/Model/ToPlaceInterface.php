<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model;

interface ToPlaceInterface
{
    public function getPlace(): int;

    public function setPlace(int $place): void;

    public function getExpression(): ?string;

    public function setExpression(?string $expression): void;
}
