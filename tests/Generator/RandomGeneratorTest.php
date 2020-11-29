<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Generator;

use Petrinet\Model\PetrinetInterface;
use Petrinet\Model\TransitionInterface;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\ColorfulMarkingInterface;
use SingleColorPetrinet\Model\ColorInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Generator\RandomGenerator;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Generator\StateInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Service\Generator\StateHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Generator\RandomGenerator
 * @covers \Tienvx\Bundle\MbtBundle\Generator\AbstractGenerator
 * @covers \Tienvx\Bundle\MbtBundle\Model\Generator\State
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 */
class RandomGeneratorTest extends TestCase
{
    protected PetrinetHelperInterface $petrinetHelper;
    protected MarkingHelperInterface $markingHelper;
    protected ModelHelperInterface $modelHelper;
    protected GuardedTransitionServiceInterface $transitionService;
    protected StateHelperInterface $stateHelper;
    protected PetrinetInterface $petrinet;
    protected ColorfulMarkingInterface $marking;
    protected ModelInterface $model;
    protected RandomGenerator $generator;
    protected StateInterface $state;
    protected array $places = [
        1 => 2,
        3 => 4,
    ];

    protected function setUp(): void
    {
        $this->petrinetHelper = $this->createMock(PetrinetHelperInterface::class);
        $this->markingHelper = $this->createMock(MarkingHelperInterface::class);
        $this->modelHelper = $this->createMock(ModelHelperInterface::class);
        $this->transitionService = $this->createMock(GuardedTransitionServiceInterface::class);
        $this->stateHelper = $this->createMock(StateHelperInterface::class);
        $this->petrinet = $this->createMock(PetrinetInterface::class);
        $this->marking = $this->createMock(ColorfulMarkingInterface::class);
        $this->model = $this->createMock(ModelInterface::class);
        $this->state = $this->createMock(StateInterface::class);
        $this->generator = new RandomGenerator(
            $this->petrinetHelper,
            $this->markingHelper,
            $this->modelHelper,
            $this->transitionService,
            $this->stateHelper
        );
    }

    public function testGetManager(): void
    {
        $this->assertSame(GeneratorManager::class, RandomGenerator::getManager());
    }

    public function testGetName(): void
    {
        $this->assertSame('random', RandomGenerator::getName());
    }

    public function testGenerateNoNextTransition(): void
    {
        $this->mockGenerate();
        $this->stateHelper->expects($this->once())->method('canStop')->willReturn(false);
        $this->stateHelper->expects($this->never())->method('update');
        $this->markingHelper->expects($this->never())->method('getPlaces');
        $this->transitionService
            ->expects($this->once())
            ->method('getEnabledTransitions')
            ->with($this->petrinet, $this->marking)
            ->willReturn([]);
        $count = 1;
        foreach ($this->generator->generate($this->model) as $step) {
            ++$count;
        }
        $this->assertSame(1, $count);
    }

    public function testGenerate(): void
    {
        $this->mockGenerate();
        $this->stateHelper->expects($this->exactly(3))->method('canStop')->willReturn(false);
        $transition1 = $this->createMock(TransitionInterface::class);
        $transition1->expects($this->once())->method('getId')->willReturn(1);
        $transition2 = $this->createMock(TransitionInterface::class);
        $transition2->expects($this->once())->method('getId')->willReturn(2);
        $this->marking->expects($this->exactly(2))->method('getColor')->willReturnOnConsecutiveCalls(
            $this->createMock(ColorInterface::class),
            $this->createMock(ColorInterface::class),
        );
        $this->transitionService
            ->expects($this->exactly(3))
            ->method('getEnabledTransitions')
            ->with($this->petrinet, $this->marking)
            ->willReturnOnConsecutiveCalls(
                [$transition1],
                [$transition2],
                [],
            );
        $this->stateHelper->expects($this->exactly(2))->method('update')->withConsecutive(
            [$this->state, $this->marking, $transition1],
            [$this->state, $this->marking, $transition2],
        );
        $this->markingHelper
            ->expects($this->exactly(2))
            ->method('getPlaces')
            ->with($this->marking)
            ->willReturn($this->places);
        $count = 1;
        foreach ($this->generator->generate($this->model) as $step) {
            ++$count;
            $this->assertInstanceOf(StepInterface::class, $step);
            $this->assertSame(2 === $count ? 1 : 2, $step->getTransition());
        }
        $this->assertSame(3, $count);
    }

    protected function mockGenerate(): void
    {
        $this->stateHelper
            ->expects($this->once())
            ->method('initState')
            ->with($this->petrinet, $this->places)
            ->willReturn($this->state);
        $this->petrinetHelper->expects($this->once())->method('build')->with($this->model)->willReturn($this->petrinet);
        $this->modelHelper
            ->expects($this->once())
            ->method('getInitPlaces')
            ->with($this->model)
            ->willReturn($this->places);
        $this->markingHelper
            ->expects($this->once())
            ->method('getMarking')
            ->with($this->petrinet, $this->places)
            ->willReturn($this->marking);
    }
}
