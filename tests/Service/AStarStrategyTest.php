<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use Petrinet\Model\Petrinet;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\Color;
use SingleColorPetrinet\Model\ColorInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug\Step;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Search\AStar;
use Tienvx\Bundle\MbtBundle\Model\Search\Node;
use Tienvx\Bundle\MbtBundle\Service\AStarStrategy;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\AStarStrategy
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @covers \Tienvx\Bundle\MbtBundle\Model\Search\Node
 */
class AStarStrategyTest extends TestCase
{
    public function testRun()
    {
        $petrinet = new Petrinet();
        $fromStep = new Step([], new Color(), 1);
        $toStep = new Step([], new Color(), 2);
        $nodes = [
            new Node([], new Color(), 3),
            new Node([], new Color(), 4),
        ];
        $transitionService = $this->createMock(GuardedTransitionServiceInterface::class);
        $markingHelper = $this->createMock(MarkingHelperInterface::class);
        $aStar = $this->createMock(AStar::class);
        $aStar
            ->expects($this->once())
            ->method('run')
            ->with($this->isInstanceOf(Node::class), $this->isInstanceOf(Node::class))
            ->willReturn($nodes);
        $aStar->expects($this->once())->method('setTransitionService')->with($transitionService);
        $aStar->expects($this->once())->method('setPetrinet')->with($petrinet);
        $starStrategy = new AStarStrategy($transitionService, $markingHelper, $aStar);
        $newSteps = $starStrategy->run($petrinet, $fromStep, $toStep);
        $count = 0;
        foreach ($newSteps as $key => $step) {
            $this->assertInstanceOf(StepInterface::class, $step);
            /** @var StepInterface $step */
            $this->assertSame([], $step->getPlaces());
            $this->assertInstanceOf(ColorInterface::class, $step->getColor());
            $this->assertSame($key === 0 ? 3 : 4, $step->getTransition());
            ++$count;
        }
        $this->assertSame(2, $count);
    }
}
