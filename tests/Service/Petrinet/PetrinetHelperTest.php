<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Petrinet;

use Petrinet\Model\PlaceInterface;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\Color;
use SingleColorPetrinet\Model\ColorfulFactory;
use SingleColorPetrinet\Model\GuardedTransitionInterface;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelper;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelper
 *
 * @uses \Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Place
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 */
class PetrinetHelperTest extends TestCase
{
    public function testBuild(): void
    {
        $factory = new ColorfulFactory();
        $expressionLanguage = new ExpressionLanguage();
        $helper = new PetrinetHelper($factory, $expressionLanguage);
        $places = [
            $place1 = new Place(),
            $place2 = new Place(),
            $place3 = new Place(),
        ];
        $revision = new Revision();
        $revision->setPlaces($places);
        $transitions = [
            $transition1 = new Transition(),
            $transition2 = new Transition(),
        ];
        $transition1->setGuard('count > 0');
        $transition1->setFromPlaces([0, 1]);
        $transition1->setToPlaces([2]);
        $transition2->setFromPlaces([2]);
        $transition2->setToPlaces([1]);
        $revision->setTransitions($transitions);
        $petrinet = $helper->build($revision);
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
        $this->assertIsCallable($guardCallback = $petrinet->getTransitions()[0]->getGuard());
        $this->assertTrue($guardCallback(new Color(['count' => 1])));
        $this->assertFalse($guardCallback(new Color(['count' => 0])));
        $this->assertNull($petrinet->getTransitions()[1]->getGuard());
        $this->assertCount(2, $petrinet->getTransitions()[0]->getInputArcs());
        $this->assertCount(1, $petrinet->getTransitions()[0]->getOutputArcs());
        $this->assertCount(1, $petrinet->getTransitions()[1]->getInputArcs());
        $this->assertCount(1, $petrinet->getTransitions()[1]->getOutputArcs());
    }
}
