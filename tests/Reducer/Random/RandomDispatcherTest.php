<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer\Random;

use Tienvx\Bundle\MbtBundle\Reducer\Random\RandomDispatcher;
use Tienvx\Bundle\MbtBundle\Tests\Reducer\DispatcherTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\Random\RandomDispatcher
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\DispatcherTemplate
 *
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @uses \Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage
 */
class RandomDispatcherTest extends DispatcherTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = new RandomDispatcher($this->messageBus);
    }
}
