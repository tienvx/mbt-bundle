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

    public function stepsProvider(): array
    {
        return [
            [0, []],
            [1, []],
            [2, []],
            [3, [
                [0, 2],
            ]],
            [4, range(1, 2)],
            [5, range(1, 3)],
            [6, range(1, 3)],
            [9, range(1, 3)],
            [11, range(1, 4)],
        ];
    }

    protected function assertPairs(array $expectedPairs): void
    {
        if (count($expectedPairs) > 1) {
            $this->assertCount(count($expectedPairs), $this->pairs);
        } else {
            $this->assertSame($expectedPairs, $this->pairs);
        }
    }
}
