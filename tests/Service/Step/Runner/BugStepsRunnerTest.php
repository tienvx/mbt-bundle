<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Step\Runner;

use Exception;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\BugStepsRunner;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Step\Runner\BugStepsRunner
 * @covers \Tienvx\Bundle\MbtBundle\Service\Step\Runner\StepsRunner
 *
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Model\Revision
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 */
class BugStepsRunnerTest extends StepsRunnerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->stepsRunner = new BugStepsRunner($this->selenoidHelper, $this->stepRunner);
    }

    protected function assertHandlingException(Exception $exception, array $bugSteps = []): void
    {
        $this->handleException
            ->expects($this->once())
            ->method('__invoke')
            ->with($exception);
    }
}
