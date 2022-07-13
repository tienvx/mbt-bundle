<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Factory\Model\Revision;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Factory\Model\Revision\TransitionFactory;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Factory\Model\Revision\TransitionFactory
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\HasCommands
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition
 */
class TransitionFactoryTest extends TestCase
{
    protected array $data;

    protected function setUp(): void
    {
        $this->data = [
            'label' => 'Transition 1',
            'guard' => 'count > 1',
            'expression' => 'count = count + 1',
            'fromPlaces' => [1, 2],
            'toPlaces' => [2, 3],
            'commands' => [],
        ];
    }

    public function testCreateFromArray(): void
    {
        $transition = TransitionFactory::createFromArray($this->data);
        $this->assertSame('Transition 1', $transition->getLabel());
        $this->assertSame('count > 1', $transition->getGuard());
        $this->assertSame('count = count + 1', $transition->getExpression());
        $this->assertSame([1, 2], $transition->getFromPlaces());
        $this->assertSame([2, 3], $transition->getToPlaces());
        $this->assertIsArray($transition->getCommands());
    }
}
