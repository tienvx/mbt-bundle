<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Step\Runner;

use Exception;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\DebugInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\ExploreStepsRunner;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Step\Runner\ExploreStepsRunner
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
class ExploreStepsRunnerTest extends StepsRunnerTestCase
{
    protected ConfigInterface $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = $this->createMock(ConfigInterface::class);
        $this->stepsRunner = new ExploreStepsRunner($this->selenoidHelper, $this->stepRunner, $this->config);
    }

    /**
     * @dataProvider entityProvider
     */
    public function testRunReachMax2Steps(DebugInterface $entity): void
    {
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($entity)
            ->willReturn($this->driver);
        $this->driver->expects($this->once())->method('quit');
        $this->assertRunSteps(array_slice($this->steps, 0, 2), null, 2);
        $this->handleException->expects($this->never())->method('__invoke');
        $this->stepsRunner->run($this->steps, $entity, $this->handleException);
    }

    protected function assertHandlingException(Exception $exception, array $bugSteps = []): void
    {
        $this->handleException
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(fn (object $bug) => $bug instanceof BugInterface &&
                '' === $bug->getTitle() &&
                $bug->getMessage() === $exception->getMessage() &&
                !array_udiff(
                    $bug->getSteps(),
                    $bugSteps,
                    fn (StepInterface $step1, StepInterface $step2) => $step1->getPlaces() === $step2->getPlaces() &&
                        $step1->getTransition() === $step2->getTransition() &&
                        $step1->getColor()->getValues() === $step2->getColor()->getValues()
                )));
    }

    protected function assertRunSteps(array $steps = [], ?Exception $exception = null, int $maxSteps = 99): void
    {
        parent::assertRunSteps($steps, $exception);
        $this->config
            ->expects($this->exactly($exception ? count($steps) - 1 : count($steps)))
            ->method('getMaxSteps')
            ->willReturn($maxSteps);
    }
}
