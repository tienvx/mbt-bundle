<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

interface BugHelperInterface
{
    public function setBugUrl(string $bugUrl): void;

    public function create(array $steps, string $message, ModelInterface $model): void;

    public function buildBugUrl(BugInterface $bug): string;
}
