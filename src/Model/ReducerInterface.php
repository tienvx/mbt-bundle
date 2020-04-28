<?php

namespace Tienvx\Bundle\MbtBundle\Model;

interface ReducerInterface
{
    public function getName(): string;

    public function setName(string $name): self;
}
