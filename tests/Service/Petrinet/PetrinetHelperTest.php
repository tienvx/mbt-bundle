<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Petrinet;

use Petrinet\Model\PlaceInterface;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\ColorfulFactory;
use SingleColorPetrinet\Model\GuardedTransitionInterface;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelper;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelper
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Transition
 */
class PetrinetHelperTest extends TestCase
{
    public function testBuild(): void
    {
        $factory = new ColorfulFactory();
        $helper = new PetrinetHelper($factory);
        $places = [
            $place1 = new Place(),
            $place2 = new Place(),
            $place3 = new Place(),
        ];
        $model = new Model();
        $model->setPlaces($places);
        $transitions = [
            $transition1 = new Transition(),
            $transition2 = new Transition(),
        ];
        $transition1->setGuard('count > 0');
        $transition1->setFromPlaces([0, 1]);
        $transition1->setToPlaces([2]);
        $transition1->setExpression('{count: 0}');
        $transition2->setFromPlaces([2]);
        $transition2->setToPlaces([1]);
        $transition2->setExpression('{count: count + 1}');
        $model->setTransitions($transitions);
        $petrinet = $helper->build($model);
        $this->assertCount(3, $petrinet->getPlaces());
        foreach ($petrinet->getPlaces() as $place) {
            $this->assertInstanceOf(PlaceInterface::class, $place);
        }
        $this->assertCount(0, $petrinet->getPlaces()[0]->getInputArcs());
        $this->assertCount(1, $petrinet->getPlaces()[0]->getOutputArcs());
        $this->assertCount(1, $petrinet->getPlaces()[1]->getInputArcs());
        $this->assertCount(1, $petrinet->getPlaces()[1]->getOutputArcs());
        $this->assertCount(1, $petrinet->getPlaces()[2]->getInputArcs());
        $this->assertCount(1, $petrinet->getPlaces()[2]->getOutputArcs());
        $this->assertCount(2, $petrinet->getTransitions());
        foreach ($petrinet->getTransitions() as $place) {
            $this->assertInstanceOf(GuardedTransitionInterface::class, $place);
        }
        $this->assertSame('count > 0', $petrinet->getTransitions()[0]->getGuard()->getExpression());
        $this->assertSame('{count: 0}', $petrinet->getTransitions()[0]->getExpression()->getExpression());
        $this->assertNull($petrinet->getTransitions()[1]->getGuard());
        $this->assertSame('{count: count + 1}', $petrinet->getTransitions()[1]->getExpression()->getExpression());
        $this->assertCount(2, $petrinet->getTransitions()[0]->getInputArcs());
        $this->assertCount(1, $petrinet->getTransitions()[0]->getOutputArcs());
        $this->assertCount(1, $petrinet->getTransitions()[1]->getInputArcs());
        $this->assertCount(1, $petrinet->getTransitions()[1]->getOutputArcs());
    }
}
