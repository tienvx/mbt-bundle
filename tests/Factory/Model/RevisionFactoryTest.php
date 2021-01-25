<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Factory\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Factory\Model\RevisionFactory;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Factory\Model\Revision\PlaceFactory
 * @covers \Tienvx\Bundle\MbtBundle\Factory\Model\Revision\TransitionFactory
 * @covers \Tienvx\Bundle\MbtBundle\Factory\Model\RevisionFactory
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\HasCommands
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 */
class RevisionFactoryTest extends TestCase
{
    protected array $data;

    protected function setUp(): void
    {
        $this->data = [
            'places' => [
                [
                    'label' => 'Place 1',
                    'commands' => [],
                ],
            ],
            'transitions' => [
                [
                    'label' => 'Transition 1',
                    'guard' => 'count > 1',
                    'fromPlaces' => [1, 2],
                    'toPlaces' => [2, 3],
                    'commands' => [],
                ],
            ],
        ];
    }

    public function testCreateFromArray(): void
    {
        $revision = RevisionFactory::createFromArray($this->data);
        $this->assertSame('Place 1', $revision->getPlaces()[0]->getLabel());
        $this->assertIsArray($revision->getPlaces()[0]->getCommands());
        $this->assertSame('Transition 1', $revision->getTransition(0)->getLabel());
        $this->assertSame('count > 1', $revision->getTransition(0)->getGuard());
        $this->assertSame([1, 2], $revision->getTransition(0)->getFromPlaces());
        $this->assertSame([2, 3], $revision->getTransition(0)->getToPlaces());
        $this->assertIsArray($revision->getTransition(0)->getCommands());
    }
}
