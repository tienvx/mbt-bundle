<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use Exception;
use Petrinet\Model\TransitionInterface;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Builder\SingleColorPetrinetBuilder;
use SingleColorPetrinet\Model\Color;
use SingleColorPetrinet\Model\ColorfulFactory;
use SingleColorPetrinet\Model\ColorfulMarkingInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsRunner;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\StepsRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 */
class StepsRunnerTest extends TestCase
{
    protected TransitionInterface $transition1;
    protected TransitionInterface $transition2;
    protected ColorfulMarkingInterface $marking1;
    protected ColorfulMarkingInterface $marking2;
    protected ModelInterface $model;
    protected array $steps;
    protected PetrinetHelperInterface $petrinetHelper;
    protected MarkingHelperInterface $markingHelper;
    protected GuardedTransitionServiceInterface $transitionService;
    protected StepRunnerInterface $stepRunner;
    protected StepsRunnerInterface $stepsRunner;

    protected function setUp(): void
    {
        $this->model = $this->createMock(ModelInterface::class);
        $factory = new ColorfulFactory();
        $builder = new SingleColorPetrinetBuilder($factory);

        $petrinet = $builder
            ->connect($place1 = $builder->place(), $this->transition1 = $builder->transition())
            ->connect($this->transition1, $place2 = $builder->place())
            ->connect($place3 = $builder->place(), $this->transition2 = $builder->transition())
            ->connect($this->transition2, $place4 = $builder->place())
            ->getPetrinet();

        $this->marking1 = $this->createMock(ColorfulMarkingInterface::class);
        $this->marking2 = $this->createMock(ColorfulMarkingInterface::class);
        $step1 = new Step([], new Color(), 0);
        $step2 = new Step([], new Color(), 1);
        $this->steps = [$step1, $step2];

        $this->petrinetHelper = $this->createMock(PetrinetHelperInterface::class);
        $this->petrinetHelper->expects($this->once())->method('build')->with($this->model)->willReturn($petrinet);
        $this->markingHelper = $this->createMock(MarkingHelperInterface::class);
        $this->markingHelper
            ->expects($this->any())
            ->method('getMarking')
            ->willReturnOnConsecutiveCalls($this->marking1, $this->marking2);
        $this->transitionService = $this->createMock(GuardedTransitionServiceInterface::class);
        $this->stepRunner = $this->createMock(StepRunnerInterface::class);
        $this->stepsRunner = new StepsRunner(
            $this->petrinetHelper,
            $this->markingHelper,
            $this->transitionService,
            $this->stepRunner
        );
    }

    public function testRun(): void
    {
        $this->transitionService
            ->expects($this->exactly(2))
            ->method('isEnabled')
            ->withConsecutive([$this->transition1, $this->marking1], [$this->transition2, $this->marking2])
            ->willReturn(true);
        $this->transitionService
            ->expects($this->exactly(2))
            ->method('fire')
            ->withConsecutive([$this->transition1, $this->marking1], [$this->transition2, $this->marking2]);
        $this->stepRunner->expects($this->once())->method('setUp');
        $this->stepRunner->expects($this->once())->method('tearDown');
        $this->stepRunner
            ->expects($this->exactly(2))
            ->method('run')
            ->withConsecutive([$this->steps[0]], [$this->steps[1]]);
        iterator_to_array($this->stepsRunner->run($this->steps, $this->model));
    }

    public function testRunDisabledTransition(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Transition 1 is not enabled');
        $this->transitionService
            ->expects($this->exactly(2))
            ->method('isEnabled')
            ->withConsecutive([$this->transition1, $this->marking1], [$this->transition2, $this->marking2])
            ->willReturnOnConsecutiveCalls(true, false);
        $this->transitionService
            ->expects($this->exactly(1))
            ->method('fire')
            ->withConsecutive([$this->transition1, $this->marking1], [$this->transition2, $this->marking2]);
        $this->stepRunner->expects($this->once())->method('setUp');
        $this->stepRunner->expects($this->once())->method('tearDown');
        $this->stepRunner->expects($this->exactly(1))->method('run')->withConsecutive([$this->steps[0]]);
        iterator_to_array($this->stepsRunner->run($this->steps, $this->model));
    }

    public function testRunFailedExecutingCommands(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Can not execute commands');
        $this->transitionService
            ->expects($this->exactly(2))
            ->method('isEnabled')
            ->withConsecutive([$this->transition1, $this->marking1], [$this->transition2, $this->marking2])
            ->willReturn(true);
        $this->transitionService
            ->expects($this->exactly(2))
            ->method('fire')
            ->withConsecutive([$this->transition1, $this->marking1], [$this->transition2, $this->marking2]);
        $this->stepRunner->expects($this->once())->method('setUp');
        $this->stepRunner->expects($this->once())->method('tearDown');
        $this->stepRunner
            ->expects($this->exactly(2))
            ->method('run')
            ->withConsecutive([$this->steps[0]], [$this->steps[1]])
            ->willReturnOnConsecutiveCalls(
                null,
                $this->throwException(new Exception('Can not execute commands'))
            );
        iterator_to_array($this->stepsRunner->run($this->steps, $this->model));
    }
}
