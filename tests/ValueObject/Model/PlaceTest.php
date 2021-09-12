<?php

namespace Tienvx\Bundle\MbtBundle\Tests\ValueObject\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\PlaceInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Place
 * @covers \Tienvx\Bundle\MbtBundle\ValueObject\Model\Place
 */
class PlaceTest extends TestCase
{
    protected PlaceInterface $place;

    protected function setUp(): void
    {
        $this->place = new Place();
        $this->place->setLabel('Place label');
    }

    public function testToString(): void
    {
        $this->assertSame('Place label', (string) $this->place);
    }
}
