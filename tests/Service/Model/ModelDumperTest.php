<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\ToPlace;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelDumper;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Model\ModelDumper
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model
 * @covers \Tienvx\Bundle\MbtBundle\ValueObject\Model\Place
 * @covers \Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition
 * @covers \Tienvx\Bundle\MbtBundle\ValueObject\Model\ToPlace
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\ToPlace
 */
class ModelDumperTest extends TestCase
{
    public function testDump(): void
    {
        $model = new Model();
        $model->setId(1);
        $model->setPlaces([
            $p1 = new Place(),
            $p2 = new Place(),
            $p3 = new Place(),
            $p4 = new Place(),
        ]);
        $p1->setLabel('p1');
        $p2->setLabel('p2');
        $p3->setLabel('p3');
        $p4->setLabel('p4');
        $model->setTransitions([
            $t1 = new Transition(),
            $t2 = new Transition(),
        ]);
        $t1->setLabel('t1');
        $t1->setFromPlaces([0]);
        $t1->setToPlaces([
            $tp1 = new ToPlace(),
            $tp2 = new ToPlace(),
        ]);
        $tp1->setPlace(1);
        $tp1->setExpression('{count: count + 1}');
        $tp2->setPlace(2);
        $tp2->setExpression('{product: "Galaxy Note"}');
        $t2->setLabel('t2');
        $t2->setFromPlaces([1, 2]);
        $t2->setToPlaces([
            $tp3 = new ToPlace(),
        ]);
        $tp3->setPlace(3);
        $t2->setGuard('count > 1');

        $graph = 'digraph "1" {
"place-0" [label="p1"]
"place-1" [label="p2"]
"place-2" [label="p3"]
"place-3" [label="p4"]
"transition-0" [label="t1" shape=box]
"transition-1" [label="t2 - (count > 1)" shape=box]
"place-0" -> "transition-0" [label=""]
"transition-0" -> "place-1" [label="({count: count + 1})"]
"transition-0" -> "place-2" [label="({product: "Galaxy Note"})"]
"place-1" -> "transition-1" [label=""]
"place-2" -> "transition-1" [label=""]
"transition-1" -> "place-3" [label=""]
}';
        $this->assertSame($graph, (new ModelDumper())->dump($model));
    }
}
