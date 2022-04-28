<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\AStar;

use Petrinet\Model\MarkingInterface;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\Color;
use SingleColorPetrinet\Model\ColorfulMarking;
use SingleColorPetrinet\Model\ColorInterface;
use SingleColorPetrinet\Model\GuardedTransition;
use SingleColorPetrinet\Model\PetrinetInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\Bug\Step;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Service\AStar\PetrinetDomainLogic;
use Tienvx\Bundle\MbtBundle\Service\AStar\PetrinetDomainLogicInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\AStar\PetrinetDomainLogic
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 */
class PetrinetDomainLogicTest extends TestCase
{
    protected GuardedTransitionServiceInterface $transitionService;
    protected MarkingHelperInterface $markingHelper;
    protected PetrinetInterface $petrinet;
    protected PetrinetDomainLogicInterface $petrinetDomainLogic;
    protected array $transitions;
    protected array $markings;
    protected array $places;

    protected function setUp(): void
    {
        $this->transitionService = $this->createMock(GuardedTransitionServiceInterface::class);
        $this->markingHelper = $this->createMock(MarkingHelperInterface::class);
        $this->petrinetDomainLogic = new PetrinetDomainLogic($this->transitionService, $this->markingHelper);
        $this->petrinet = $this->createMock(PetrinetInterface::class);
        $this->transitions = [
            $transition1 = new GuardedTransition(),
            $transition2 = new GuardedTransition(),
            $transition3 = new GuardedTransition(),
            $transition4 = new GuardedTransition(),
        ];
        $transition1->setId(0);
        $transition2->setId(1);
        $transition3->setId(2);
        $transition4->setId(3);
        $this->markings = [
            $marking1 = new ColorfulMarking(),
            $marking2 = new ColorfulMarking(),
            $marking3 = new ColorfulMarking(),
            $marking4 = new ColorfulMarking(),
            $marking5 = new ColorfulMarking(),
        ];
        $marking2->setColor(new Color(['key 1' => 'value 1']));
        $marking3->setColor(new Color(['key 2' => 'value 2']));
        $marking4->setColor(new Color(['key 3' => 'value 3']));
        $marking5->setColor(new Color(['key 4' => 'value 4']));
        $this->places = [
            [1 => 11],
            [2 => 22],
            [3 => 33],
            [4 => 44],
        ];
    }

    /**
     * @dataProvider invalidNodeProvider
     */
    public function testCalculateEstimatedCostInvalidNode(mixed $fromNode, mixed $toNode): void
    {
        $this->expectExceptionObject(new RuntimeException('The provided nodes are invalid'));
        $this->petrinetDomainLogic->calculateEstimatedCost($fromNode, $toNode);
    }

    public function invalidNodeProvider(): array
    {
        $step = new Step([], new Color(), 0);

        return [
            [$step, 'to node'],
            ['from node', $step],
            ['from node', 'to node'],
        ];
    }

    /**
     * @dataProvider estimatedCostProvider
     */
    public function testCalculateEstimatedCost(Step $fromNode, Step $toNode, int $cost): void
    {
        $this->assertSame($cost, $this->petrinetDomainLogic->calculateEstimatedCost($fromNode, $toNode));
    }

    public function estimatedCostProvider(): array
    {
        $color = new Color(['key' => 'value']);
        $differentColor = new Color(['different key' => 'different value']);

        return [
            [$this->getStep([0 => 1, 1 => 5, 2 => 3], $color), $this->getStep([], $color), 9],
            [$this->getStep([], $color), $this->getStep([0 => 3, 1 => 2, 2 => 2, 3 => 1], $color), 8],
            [$this->getStep([0 => 2, 1 => 4], $color), $this->getStep([1 => 5, 2 => 3, 3 => 1], $color), 7],
            [$this->getStep([0 => 4, 1 => 1, 2 => 2], $color), $this->getStep([2 => 5], $differentColor), 16],
            [$this->getStep([], $color), $this->getStep([0 => 4, 1 => 1], $differentColor), 10],
            [
                $this->getStep([0 => 3, 1 => 2, 2 => 2], $color),
                $this->getStep([1 => 7, 2 => 2, 3 => 3], $differentColor),
                22,
            ],
        ];
    }

    public function testCalculateRealCost(): void
    {
        $this->assertSame(1, $this->petrinetDomainLogic->calculateRealCost('node', 'adjacent'));
    }

    public function testGetAdjacentNodesOfInvalidNode(): void
    {
        $this->expectExceptionObject(new RuntimeException('The provided node is invalid'));
        $this->petrinetDomainLogic->getAdjacentNodes('invalid');
    }

    public function testGetAdjacentNodesWithoutPetrinet(): void
    {
        $this->expectExceptionObject(new RuntimeException('Petrinet is required'));
        $this->petrinetDomainLogic->getAdjacentNodes($this->getStep([], new Color()));
    }

    public function testGetAdjacentNodes(): void
    {
        $node = $this->getStep([12 => 34], new Color(['key' => 'value']));
        $this->petrinetDomainLogic->setPetrinet($this->petrinet);
        $this->markingHelper
            ->expects($this->exactly(count($this->transitions) + 1))
            ->method('getMarking')
            ->with($this->petrinet, $node->getPlaces(), $node->getColor())
            ->willReturnOnConsecutiveCalls(...$this->markings);
        $this->transitionService
            ->expects($this->once())
            ->method('getEnabledTransitions')
            ->with($this->petrinet, $this->markings[0])
            ->willReturn($this->transitions);
        $this->transitionService
            ->expects($this->exactly(count($this->transitions)))
            ->method('fire')
            ->withConsecutive(...array_map(
                fn (int $index) => [$this->transitions[$index], $this->markings[$index + 1]],
                array_keys($this->transitions)
            ));
        $this->markingHelper
            ->expects($this->exactly(count($this->transitions)))
            ->method('getPlaces')
            ->withConsecutive(...array_map(
                fn (MarkingInterface $marking) => [$marking],
                array_slice($this->markings, 1, count($this->transitions))
            ))
            ->willReturnOnConsecutiveCalls(...$this->places);
        $adjacents = $this->petrinetDomainLogic->getAdjacentNodes($node);
        $this->assertCount(count($this->transitions), $adjacents);
        foreach ($adjacents as $index => $adjacent) {
            $this->assertInstanceOf(StepInterface::class, $adjacent);
            $this->assertSame($this->places[$index], $adjacent->getPlaces());
            $this->assertSame($this->markings[$index + 1]->getColor(), $adjacent->getColor());
            $this->assertSame($this->transitions[$index]->getId(), $adjacent->getTransition());
        }
    }

    protected function getStep(array $places, ColorInterface $color): StepInterface
    {
        return new Step($places, $color, 0);
    }
}
