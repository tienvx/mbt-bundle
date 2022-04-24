<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Step\Runner;

use Tienvx\Bundle\MbtBundle\Service\Step\Runner\RecordStepsRunner;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Step\Runner\RecordStepsRunner
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
class RecordStepsRunnerTest extends BugStepsRunnerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->stepsRunner = new RecordStepsRunner($this->selenoidHelper, $this->stepRunner);
    }
}
