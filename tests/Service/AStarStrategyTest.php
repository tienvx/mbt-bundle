<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug\Step;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Search\AStar;
use Tienvx\Bundle\MbtBundle\Model\Search\Node;
use Tienvx\Bundle\MbtBundle\Service\AStarStrategy;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Factory;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\AStarStrategy
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Model\Search\Node
 */
class AStarStrategyTest extends TestCase
{
    public function testRun()
    {
        $factory = Factory::createColorfulFactory();
        $petrinet = $factory->createPetrinet();
        $transition1 = $factory->createTransition();
        $transition2 = $factory->createTransition();
        $transition3 = $factory->createTransition();
        $transition4 = $factory->createTransition();
        $marking = $factory->createMarking();
        $fromStep = new Step($marking, $transition1);
        $toStep = new Step($marking, $transition2);
        $nodes = [
            new Node($marking, $transition3),
            new Node($marking, $transition4),
        ];
        $transitionService = $this->createMock(GuardedTransitionServiceInterface::class);
        $aStar = $this->createMock(AStar::class);
        $aStar->expects($this->once())->method('run')->with($this->isInstanceOf(Node::class), $this->isInstanceOf(Node::class))->willReturn($nodes);
        $aStar->expects($this->once())->method('setTransitionService')->with($transitionService);
        $aStar->expects($this->once())->method('setPetrinet')->with($petrinet);
        $starStrategy = new AStarStrategy($transitionService, $aStar);
        $newSteps = $starStrategy->run($petrinet, $fromStep, $toStep);
        $count = 0;
        foreach ($newSteps as $key => $step) {
            $this->assertInstanceOf(StepInterface::class, $step);
            $this->assertSame($marking, $step->getMarking());
            if (0 === $key) {
                $this->assertSame($transition3, $step->getTransition());
            }
            if (1 === $key) {
                $this->assertSame($transition4, $step->getTransition());
            }
            ++$count;
        }
        $this->assertSame(2, $count);
    }
}
