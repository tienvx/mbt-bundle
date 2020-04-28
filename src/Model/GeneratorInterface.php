<?php

namespace Tienvx\Bundle\MbtBundle\Model;

interface GeneratorInterface
{
    public function getName(): string;

    public function setName(string $name): self;
}
