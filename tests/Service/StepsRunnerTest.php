<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use Exception;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug\Step;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\MarkingInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsRunner;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Factory;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\StepsRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Transition
 */
class StepsRunnerTest extends TestCase
{
    protected TransitionInterface $transition1;
    protected TransitionInterface $transition2;
    protected MarkingInterface $marking1;
    protected MarkingInterface $marking2;
    protected array $steps;
    protected GuardedTransitionServiceInterface $transitionService;
    protected StepRunnerInterface $stepRunner;
    protected StepsRunnerInterface $stepsRunner;

    protected function setUp(): void
    {
        $factory = Factory::createColorfulFactory();
        $this->transition1 = $factory->createTransition();
        $this->transition2 = $factory->createTransition();
        $this->transition2->setId(2);
        $this->marking1 = $this->createMock(MarkingInterface::class);
        $this->marking2 = $this->createMock(MarkingInterface::class);
        $step1 = new Step($this->marking1, $this->transition1);
        $step2 = new Step($this->marking2, $this->transition2);
        $this->steps = [$step1, $step2];

        $this->transitionService = $this->createMock(GuardedTransitionServiceInterface::class);
        $this->stepRunner = $this->createMock(StepRunnerInterface::class);
    }

    public function testRun(): void
    {
        $this->transitionService->expects($this->exactly(2))->method('isEnabled')->withConsecutive([$this->transition1, $this->marking1], [$this->transition2, $this->marking2])->willReturn(true);
        $this->transitionService->expects($this->exactly(2))->method('fire')->withConsecutive([$this->transition1, $this->marking1], [$this->transition2, $this->marking2]);
        $this->stepRunner->expects($this->once())->method('setUp');
        $this->stepRunner->expects($this->once())->method('tearDown');
        $this->stepRunner->expects($this->exactly(2))->method('run')->withConsecutive([$this->steps[0]], [$this->steps[1]]);
        $stepsRunner = new StepsRunner($this->transitionService, $this->stepRunner);
        iterator_to_array($stepsRunner->run($this->steps));
    }

    public function testRunDisabledTransition(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Transition 2 is not enabled');
        $this->transitionService->expects($this->exactly(2))->method('isEnabled')->withConsecutive([$this->transition1, $this->marking1], [$this->transition2, $this->marking2])->willReturnOnConsecutiveCalls(true, false);
        $this->transitionService->expects($this->exactly(1))->method('fire')->withConsecutive([$this->transition1, $this->marking1], [$this->transition2, $this->marking2]);
        $this->stepRunner->expects($this->once())->method('setUp');
        $this->stepRunner->expects($this->once())->method('tearDown');
        $this->stepRunner->expects($this->exactly(1))->method('run')->withConsecutive([$this->steps[0]]);
        $stepsRunner = new StepsRunner($this->transitionService, $this->stepRunner);
        iterator_to_array($stepsRunner->run($this->steps));
    }

    public function testRunFailedExecutingCommands(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Can not execute commands');
        $this->transitionService->expects($this->exactly(2))->method('isEnabled')->withConsecutive([$this->transition1, $this->marking1], [$this->transition2, $this->marking2])->willReturn(true);
        $this->transitionService->expects($this->exactly(2))->method('fire')->withConsecutive([$this->transition1, $this->marking1], [$this->transition2, $this->marking2]);
        $this->stepRunner->expects($this->once())->method('setUp');
        $this->stepRunner->expects($this->once())->method('tearDown');
        $this->stepRunner->expects($this->exactly(2))->method('run')->withConsecutive([$this->steps[0]], [$this->steps[1]])->willReturnOnConsecutiveCalls(null, $this->throwException(new Exception('Can not execute commands')));
        $stepsRunner = new StepsRunner($this->transitionService, $this->stepRunner);
        iterator_to_array($stepsRunner->run($this->steps));
    }
}
