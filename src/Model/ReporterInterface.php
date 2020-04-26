<?php

namespace Tienvx\Bundle\MbtBundle\Model;

interface ReporterInterface
{
    public function getName(): string;

    public function setName(string $name): self;
}
