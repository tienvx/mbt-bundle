<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Step\Runner;

use Exception;
use SingleColorPetrinet\Model\ColorfulMarkingInterface;
use SingleColorPetrinet\Model\GuardedTransitionInterface;
use SingleColorPetrinet\Model\PetrinetInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\DebugInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\ReduceStepsRunner;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Step\Runner\ReduceStepsRunner
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
class ReduceStepsRunnerTest extends BugStepsRunnerTestCase
{
    protected PetrinetHelperInterface $petrinetHelper;
    protected MarkingHelperInterface $markingHelper;
    protected GuardedTransitionServiceInterface $transitionService;
    protected PetrinetInterface $petrinet;
    protected ColorfulMarkingInterface $marking;
    protected array $transitions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->petrinetHelper = $this->createMock(PetrinetHelperInterface::class);
        $this->markingHelper = $this->createMock(MarkingHelperInterface::class);
        $this->transitionService = $this->createMock(GuardedTransitionServiceInterface::class);
        $this->stepsRunner = new ReduceStepsRunner(
            $this->selenoidHelper,
            $this->stepRunner,
            $this->petrinetHelper,
            $this->markingHelper,
            $this->transitionService
        );
        $this->petrinet = $this->createMock(PetrinetInterface::class);
        $this->petrinetHelper
            ->expects($this->once())
            ->method('build')
            ->with($this->revision)
            ->willReturn($this->petrinet);
        $this->marking = $this->createMock(ColorfulMarkingInterface::class);
        $this->transitions = [
            0 => $this->createMock(GuardedTransitionInterface::class),
            1 => $this->createMock(GuardedTransitionInterface::class),
            2 => $this->createMock(GuardedTransitionInterface::class),
            3 => $this->createMock(GuardedTransitionInterface::class),
        ];
    }

    /**
     * @dataProvider entityProvider
     */
    public function testCanNotRunThirdStep(DebugInterface $entity): void
    {
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($entity)
            ->willReturn($this->driver);
        $this->driver->expects($this->once())->method('quit');
        $this->assertRunSteps(array_slice($this->steps, 0, 2), null, true);
        $this->handleException->expects($this->never())->method('__invoke');
        $this->stepsRunner->run($this->steps, $entity, $this->handleException);
    }

    protected function assertRunSteps(
        array $steps = [],
        ?Exception $exception = null,
        bool $nextStepDisabled = false
    ): void {
        parent::assertRunSteps($steps, $exception);
        $this->markingHelper
            ->expects($this->exactly(count($steps) + $nextStepDisabled))
            ->method('getMarking')
            ->withConsecutive(...array_map(
                fn (StepInterface $step) => [$this->petrinet, $step->getPlaces(), $step->getColor()],
                array_slice($this->steps, 0, count($steps) + $nextStepDisabled)
            ))
            ->willReturn($this->marking);
        $this->petrinet
            ->expects($this->exactly(count($steps) + $nextStepDisabled))
            ->method('getTransitionById')
            ->willReturnCallback(fn (int $id) => $this->transitions[$id]);
        if (count($steps) < count($this->steps)) {
            $this->transitionService
                ->expects($this->exactly(count($steps) + $nextStepDisabled))
                ->method('isEnabled')
                ->withConsecutive(...array_map(
                    fn (StepInterface $step) => [$this->transitions[$step->getTransition()], $this->marking],
                    array_slice($this->steps, 0, count($steps) + $nextStepDisabled)
                ))
                ->willReturnOnConsecutiveCalls(...[...array_fill(0, count($steps), true), !$nextStepDisabled]);
        } else {
            $this->transitionService
                ->expects($this->exactly(count($this->steps)))
                ->method('isEnabled')
                ->withConsecutive(...array_map(
                    fn (StepInterface $step) => [$this->transitions[$step->getTransition()], $this->marking],
                    $this->steps
                ))
                ->willReturn(true);
        }
    }
}
