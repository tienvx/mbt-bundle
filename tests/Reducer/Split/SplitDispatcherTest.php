<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer\Split;

use Tienvx\Bundle\MbtBundle\Reducer\Split\SplitDispatcher;
use Tienvx\Bundle\MbtBundle\Tests\Reducer\DispatcherTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\Split\SplitDispatcher
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\DispatcherTemplate
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Steps
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage
 */
class SplitDispatcherTest extends DispatcherTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = new SplitDispatcher($this->messageBus);
    }

    protected function assertPairs(): void
    {
        parent::assertPairs();
        $this->assertSame([0, 4], $this->pairs[0]);
        $this->assertSame([4, 8], $this->pairs[1]);
        $this->assertSame([8, 10], $this->pairs[2]);
    }
}
