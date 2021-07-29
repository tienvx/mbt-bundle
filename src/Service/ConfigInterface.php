<?php

namespace Tienvx\Bundle\MbtBundle\Service;

interface ConfigInterface
{
    public function getGenerator(): string;

    public function getReducer(): string;

    public function shouldNotifyAuthor(): bool;

    public function getNotifyChannels(): array;

    public function getMaxSteps(): int;
}
