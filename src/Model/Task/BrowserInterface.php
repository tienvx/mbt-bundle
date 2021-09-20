<?php

namespace Tienvx\Bundle\MbtBundle\Model\Task;

interface BrowserInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getVersion(): string;

    public function setVersion(string $version): void;
}
