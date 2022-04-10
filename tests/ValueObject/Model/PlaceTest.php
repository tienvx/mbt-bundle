<?php

namespace Tienvx\Bundle\MbtBundle\Tests\ValueObject\Model;

use Tienvx\Bundle\MbtBundle\Model\Model\Revision\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Tests\Model\Model\Revision\PlaceTest as PlaceModelTest;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;

/**
 * @covers \Tienvx\Bundle\MbtBundle\ValueObject\Model\Place
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Place
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 * @uses \Tienvx\Bundle\MbtBundle\Factory\Model\Revision\CommandFactory
 */
class PlaceTest extends PlaceModelTest
{
    protected function createPlace(): PlaceInterface
    {
        return new Place();
    }
}
