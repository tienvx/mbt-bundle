<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelper;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Model\ModelHelper
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model
 * @covers \Tienvx\Bundle\MbtBundle\ValueObject\Model\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Place
 */
class ModelHelperTest extends TestCase
{
    public function testGetStartingPlaces(): void
    {
        $model = new Model();
        $model->setId(1);
        $places = [
            $p1 = new Place(),
            $p2 = new Place(),
            $p3 = new Place(),
            $p4 = new Place(),
        ];
        $p1->setStart(false);
        $p2->setStart(true);
        $p3->setStart(true);
        $p4->setStart(false);
        $model->setPlaces($places);

        $this->assertSame([
            1 => 1,
            2 => 1,
        ], (new ModelHelper())->getStartingPlaces($model));
    }
}
