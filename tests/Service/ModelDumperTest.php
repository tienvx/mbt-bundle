<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use Petrinet\Builder\MarkingBuilder;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Builder\SingleColorPetrinetBuilder;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\InputArcInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\OutputArcInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PetrinetInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Service\ModelDumper;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Factory;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\ModelDumper
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\InputArc
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\OutputArc
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Petrinet
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Transition
 */
class ModelDumperTest extends TestCase
{
    public function testDump(): void
    {
        $factory = Factory::createColorfulFactory();
        $builder = new SingleColorPetrinetBuilder($factory);

        $petrinet = $builder
            ->connect($p1 = $builder->place(), $t1 = $builder->transition())
            ->connect($t1, $p2 = $builder->place())
            ->connect($t1, $p3 = $builder->place())
            ->connect($p2, $t2 = $builder->transition())
            ->connect($p3, $t2)
            ->connect($t2, $p4 = $builder->place())
            ->getPetrinet();

        $this->assertInstanceOf(PetrinetInterface::class, $petrinet);
        $petrinet->setId(1);

        $this->assertInstanceOf(PlaceInterface::class, $p1);
        $p1->setLabel('p1');
        $p1->setId(1);

        $this->assertInstanceOf(PlaceInterface::class, $p2);
        $p2->setLabel('p2');
        $p2->setId(2);

        $this->assertInstanceOf(PlaceInterface::class, $p3);
        $p3->setLabel('p3');
        $p3->setId(3);

        $this->assertInstanceOf(PlaceInterface::class, $p4);
        $p4->setLabel('p4');
        $p4->setId(4);

        $this->assertInstanceOf(TransitionInterface::class, $t1);
        $t1->setLabel('t1');
        $t1->setId(1);

        $this->assertInstanceOf(TransitionInterface::class, $t2);
        $t2->setGuard($factory->createExpression('count > 1'));
        $t2->setLabel('t2');
        $t2->setId(2);

        $color = $factory->createColor([
            'count' => 0,
            'product' => 'Iphone',
        ]);
        $markingBuilder = new MarkingBuilder($factory);
        $markingBuilder->mark($p1, 1, $color);

        $this->assertInstanceOf(InputArcInterface::class, $t1->getInputArcs()[0]);
        $t1->getInputArcs()[0]->setId(1);

        $this->assertInstanceOf(OutputArcInterface::class, $t1->getOutputArcs()[0]);
        $t1->getOutputArcs()[0]->setExpression($factory->createExpression('{count: count + 1}'));
        $t1->getOutputArcs()[0]->setId(2);

        $this->assertInstanceOf(OutputArcInterface::class, $t1->getOutputArcs()[1]);
        $t1->getOutputArcs()[1]->setExpression($factory->createExpression('{product: "Galaxy Note"}'));
        $t1->getOutputArcs()[0]->setId(3);

        $this->assertInstanceOf(InputArcInterface::class, $t2->getInputArcs()[0]);
        $t2->getInputArcs()[0]->setId(4);

        $this->assertInstanceOf(InputArcInterface::class, $t2->getInputArcs()[1]);
        $t2->getInputArcs()[1]->setId(5);

        $this->assertInstanceOf(OutputArcInterface::class, $t2->getOutputArcs()[0]);
        $t2->getOutputArcs()[0]->setId(6);

        $graph =
            'digraph "1" {'."\n".
            '"1" [label="p1"]'."\n".
            '"2" [label="p2"]'."\n".
            '"3" [label="p3"]'."\n".
            '"4" [label="p4"]'."\n".
            '"1" [label="t1" shape=box]'."\n".
            '"2" [label="t2 - (count > 1)" shape=box]'."\n".
            '"p1" -> "t1" [label=""]'."\n".
            '"t1" -> "p2" [label="({count: count + 1})"]'."\n".
            '"p2" -> "t2" [label=""]'."\n".
            '"t1" -> "p3" [label="({product: "Galaxy Note"})"]'."\n".
            '"p3" -> "t2" [label=""]'."\n".
            '"t2" -> "p4" [label=""]'."\n".
            '}';
        $this->assertSame($graph, (new ModelDumper())->dump($petrinet, $markingBuilder->getMarking()));
    }
}
