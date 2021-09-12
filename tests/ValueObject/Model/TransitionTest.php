<?php

namespace Tienvx\Bundle\MbtBundle\Tests\ValueObject\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\TransitionInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition
 * @covers \Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition
 */
class TransitionTest extends TestCase
{
    protected TransitionInterface $transition;

    protected function setUp(): void
    {
        $this->transition = new Transition();
        $this->transition->setLabel('Transition label');
    }

    public function testToString(): void
    {
        $this->assertSame('Transition label', (string) $this->transition);
    }
}
