<?php

namespace Tienvx\Bundle\MbtBundle\Model;

interface WorkflowInterface
{
    public function getName(): string;

    public function setName(string $name): self;
}
