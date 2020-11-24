<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

interface StepRunnerInterface
{
    public function setUp(): void;

    public function tearDown(): void;

    public function canRun(): bool;

    public function run(StepInterface $step, ModelInterface $model): void;
}
