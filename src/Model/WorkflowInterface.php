<?php

namespace Tienvx\Bundle\MbtBundle\Model;

interface WorkflowInterface
{
    public function getName(): string;

    public function setName(string $name): self;

    public function getLabel(): string;

    public function setLabel(string $label): self;

    public function getType(): string;

    public function setType(string $type): self;
}
