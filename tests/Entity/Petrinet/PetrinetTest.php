<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity\Petrinet;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\Petrinet;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\Place;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Petrinet\Petrinet
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Petrinet\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Petrinet
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Place
 */
class PetrinetTest extends TestCase
{
    public function testIsInitPlaceIdsValid(): void
    {
        $place1 = new Place();
        $place1->setId(1);
        $place2 = new Place();
        $place2->setId(2);
        $place3 = new Place();
        $place3->setId(3);
        $petrinet = new Petrinet();
        $petrinet->setPlaces([
            $place1,
            $place2,
            $place3,
        ]);
        $petrinet->setInitPlaceIds([]);
        $this->assertFalse($petrinet->isInitPlaceIdsValid());
        $petrinet->setInitPlaceIds([4]);
        $this->assertFalse($petrinet->isInitPlaceIdsValid());
        $petrinet->setInitPlaceIds([1, 4]);
        $this->assertFalse($petrinet->isInitPlaceIdsValid());
        $petrinet->setInitPlaceIds([2]);
        $this->assertTrue($petrinet->isInitPlaceIdsValid());
        $petrinet->setInitPlaceIds([2, 3]);
        $this->assertTrue($petrinet->isInitPlaceIdsValid());
    }
}
