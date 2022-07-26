<?php

namespace Tienvx\Bundle\MbtBundle\Service;

interface ConfigInterface
{
    public function getGenerator(): string;

    public function getReducer(): string;

    public function shouldReportBug(): bool;

    public function getMaxSteps(): int;

    public function shouldCreateNewBugWhileReducing(): bool;

    public function getDefaultBugTitle(): string;
}
