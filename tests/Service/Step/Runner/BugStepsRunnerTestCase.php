<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Step\Runner;

use Exception;

abstract class BugStepsRunnerTestCase extends StepsRunnerTestCase
{
    protected function assertHandlingException(Exception $exception, array $bugSteps = []): void
    {
        $this->handleException
            ->expects($this->once())
            ->method('__invoke')
            ->with($exception);
    }
}
