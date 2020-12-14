<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Generator;

use Petrinet\Model\PetrinetInterface;
use Petrinet\Model\PlaceInterface;
use Petrinet\Model\PlaceMarkingInterface;
use Petrinet\Model\TransitionInterface;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\ColorfulMarking;
use SingleColorPetrinet\Model\ColorInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Generator\RandomGenerator;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Generator\StateInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Generator\RandomGenerator
 * @covers \Tienvx\Bundle\MbtBundle\Generator\AbstractGenerator
 * @covers \Tienvx\Bundle\MbtBundle\Model\Generator\State
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\TaskConfig
 */
class RandomGeneratorTest extends TestCase
{
    protected PetrinetHelperInterface $petrinetHelper;
    protected MarkingHelperInterface $markingHelper;
    protected ModelHelperInterface $modelHelper;
    protected GuardedTransitionServiceInterface $transitionService;
    protected PetrinetInterface $petrinet;
    protected ColorfulMarking $marking;
    protected ModelInterface $model;
    protected TaskInterface $task;
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
        $this->petrinet = $this->createMock(PetrinetInterface::class);
        $this->marking = $this->createMock(ColorfulMarking::class);
        $this->model = $this->createMock(ModelInterface::class);
        $this->task = new Task();
        $this->task->setModel($this->model);
        $this->state = $this->createMock(StateInterface::class);
        $this->generator = new RandomGenerator(
            $this->petrinetHelper,
            $this->markingHelper,
            $this->modelHelper,
            $this->transitionService
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

    /**
     * @dataProvider configProvider
     */
    public function testValidate(array $config, bool $valid)
    {
        $this->assertSame($valid, $this->generator->validate($config));
    }

    public function testGenerateNoNextTransition(): void
    {
        $this->mockGenerate();
        $this->task->getTaskConfig()->setGeneratorConfig([
            RandomGenerator::MAX_PLACE_COVERAGE => 0.1,
            RandomGenerator::MAX_TRANSITION_COVERAGE => 0.1,
        ]);
        $this->markingHelper->expects($this->never())->method('getPlaces');
        $this->transitionService
            ->expects($this->once())
            ->method('getEnabledTransitions')
            ->with($this->petrinet, $this->marking)
            ->willReturn([]);
        $count = 1;
        foreach ($this->generator->generate($this->task) as $step) {
            ++$count;
        }
        $this->assertSame(1, $count);
    }

    public function testGenerate(): void
    {
        $this->mockGenerate();
        $this->task->getTaskConfig()->setGeneratorConfig([
            RandomGenerator::MAX_PLACE_COVERAGE => 100,
            RandomGenerator::MAX_TRANSITION_COVERAGE => 100,
        ]);
        $transition1 = $this->createMock(TransitionInterface::class);
        $transition1->expects($this->exactly(2))->method('getId')->willReturn(1);
        $transition2 = $this->createMock(TransitionInterface::class);
        $transition2->expects($this->exactly(2))->method('getId')->willReturn(2);
        $this->marking->expects($this->exactly(2))->method('getColor')->willReturnOnConsecutiveCalls(
            $this->createMock(ColorInterface::class),
            $this->createMock(ColorInterface::class),
        );
        $this->marking->expects($this->exactly(2))->method('getPlaceMarkings')->willReturnOnConsecutiveCalls(
            [$placeMarking1 = $this->createMock(PlaceMarkingInterface::class)],
            [$placeMarking2 = $this->createMock(PlaceMarkingInterface::class)],
        );
        $place1 = $this->createMock(PlaceInterface::class);
        $place1->expects($this->once())->method('getId')->willReturn(1);
        $placeMarking1->expects($this->once())->method('getPlace')->willReturn($place1);
        $placeMarking1->expects($this->once())->method('getTokens')->willReturn([1]);
        $place2 = $this->createMock(PlaceInterface::class);
        $place2->expects($this->once())->method('getId')->willReturn(2);
        $placeMarking2->expects($this->once())->method('getPlace')->willReturn($place2);
        $placeMarking2->expects($this->once())->method('getTokens')->willReturn([2, 2]);
        $this->transitionService
            ->expects($this->exactly(3))
            ->method('getEnabledTransitions')
            ->with($this->petrinet, $this->marking)
            ->willReturnOnConsecutiveCalls(
                [$transition1],
                [$transition2],
                [],
            );
        $this->markingHelper
            ->expects($this->exactly(2))
            ->method('getPlaces')
            ->with($this->marking)
            ->willReturn($this->places);
        $count = 1;
        foreach ($this->generator->generate($this->task) as $step) {
            ++$count;
            $this->assertInstanceOf(StepInterface::class, $step);
            $this->assertSame(2 === $count ? 1 : 2, $step->getTransition());
        }
        $this->assertSame(3, $count);
    }

    protected function mockGenerate(): void
    {
        $this->petrinet->expects($this->once())->method('getPlaces')->willReturn(array_fill(0, 12, 1));
        $this->petrinet->expects($this->once())->method('getTransitions')->willReturn(array_fill(0, 13, 2));
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

    public function configProvider(): array
    {
        return [
            [[], false],
            [['max_place_coverage' => 55], false],
            [['max_place_coverage' => 55.7], false],
            [['max_transition_coverage' => 66], false],
            [['max_transition_coverage' => 66.8], false],
            [['max_place_coverage' => 100.1, 'max_transition_coverage' => 101.2], false],
            [['max_place_coverage' => 55.7, 'max_transition_coverage' => 66.8], true],
        ];
    }
}
