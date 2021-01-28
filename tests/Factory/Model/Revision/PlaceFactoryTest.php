<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Factory\Model\Revision;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Factory\Model\Revision\PlaceFactory;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Factory\Model\Revision\PlaceFactory
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\HasCommands
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Place
 */
class PlaceFactoryTest extends TestCase
{
    protected array $data;

    protected function setUp(): void
    {
        $this->data = [
            'label' => 'Place 1',
            'commands' => [],
        ];
    }

    public function testCreateFromArray(): void
    {
        $place = PlaceFactory::createFromArray($this->data);
        $this->assertSame('Place 1', $place->getLabel());
        $this->assertIsArray($place->getCommands());
    }
}
