<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Petrinet;

use Petrinet\Model\ArcInterface;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\Color;
use SingleColorPetrinet\Model\ColorfulFactory;
use SingleColorPetrinet\Model\GuardedTransition as PetrinetTransition;
use SingleColorPetrinet\Model\Place as PetrinetPlace;
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

        // Model revision
        $revision = new Revision();
        $revision->setPlaces([
            $place0 = new Place(),
            $place1 = new Place(),
            $place2 = new Place(),
        ]);
        $revision->setTransitions([
            $transition0 = new Transition(),
            $transition1 = new Transition(),
            $transition2 = new Transition(),
            $transition3 = new Transition(),
        ]);
        $transition0->setFromPlaces([]);
        $transition0->setToPlaces([0]);
        $transition1->setGuard('count > 0');
        $transition1->setFromPlaces([0]);
        $transition1->setToPlaces([1, 2]);
        $transition2->setFromPlaces([2]);
        $transition2->setToPlaces([1]);
        $transition3->setExpression('{count: count + 1, status: "open"}');
        $transition3->setFromPlaces([1]);
        $transition3->setToPlaces([0, 2]);

        // Petrinet
        $petrinet = $helper->build($revision);
        $this->assertCount(3, $petrinet->getPlaces());
        $places = [
            0 => [
                'input' => [3],
                'output' => [1],
            ],
            1 => [
                'input' => [1, 2],
                'output' => [3],
            ],
            2 => [
                'input' => [1, 3],
                'output' => [2],
            ],
        ];
        foreach ($petrinet->getPlaces() as $index => $place) {
            $this->assertInstanceOf(PetrinetPlace::class, $place);
            $this->assertSame(
                $places[$index]['input'],
                array_map(fn (ArcInterface $arc) => $arc->getTransition()->getId(), $place->getInputArcs()->toArray()),
            );
            $this->assertSame(
                $places[$index]['output'],
                array_map(fn (ArcInterface $arc) => $arc->getTransition()->getId(), $place->getOutputArcs()->toArray()),
            );
        }
        $this->assertCount(3, $petrinet->getTransitions());
        $transitions = [
            0 => [
                'input' => [0],
                'output' => [1, 2],
            ],
            1 => [
                'input' => [2],
                'output' => [1],
            ],
            2 => [
                'input' => [1],
                'output' => [0, 2],
            ],
        ];
        foreach ($petrinet->getTransitions() as $index => $transition) {
            $this->assertInstanceOf(PetrinetTransition::class, $transition);
            $this->assertSame(
                $transitions[$index]['input'],
                array_map(fn (ArcInterface $arc) => $arc->getPlace()->getId(), $transition->getInputArcs()->toArray()),
            );
            $this->assertSame(
                $transitions[$index]['output'],
                array_map(fn (ArcInterface $arc) => $arc->getPlace()->getId(), $transition->getOutputArcs()->toArray()),
            );
            if (0 === $index) {
                $this->assertIsCallable($guardCallback = $transition->getGuard());
                $this->assertTrue($guardCallback(new Color(['count' => 1])));
                $this->assertFalse($guardCallback(new Color(['count' => 0])));
            } else {
                $this->assertNull($transition->getGuard());
            }
            if (2 === $index) {
                $this->assertIsCallable($expressionCallback = $transition->getExpression());
                $this->assertSame(['count' => 2, 'status' => 'open'], $expressionCallback(new Color(['count' => 1])));
                $this->assertSame(['count' => 1, 'status' => 'open'], $expressionCallback(new Color(['count' => 0])));
            } else {
                $this->assertNull($transition->getExpression());
            }
        }
    }
}
