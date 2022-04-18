<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer\Split;

use Tienvx\Bundle\MbtBundle\Reducer\Split\SplitDispatcher;
use Tienvx\Bundle\MbtBundle\Tests\Reducer\DispatcherTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\Split\SplitDispatcher
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\DispatcherTemplate
 *
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @uses \Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage
 */
class SplitDispatcherTest extends DispatcherTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = new SplitDispatcher($this->messageBus);
    }

    public function stepsProvider(): array
    {
        return [
            [0, []],
            [1, []],
            [2, []],
            [3, []],
            [4, []],
            [5, [
                [0, 2],
                [2, 4],
            ]],
            [6, [
                [0, 2],
                [2, 4],
            ]],
            [7, [
                [0, 3],
                [3, 6],
            ]],
            [8, [
                [0, 3],
                [3, 6],
            ]],
            [9, [
                [0, 3],
                [3, 6],
                [6, 8],
            ]],
            [10, [
                [0, 3],
                [3, 6],
                [6, 9],
            ]],
            [11, [
                [0, 3],
                [3, 6],
                [6, 9],
            ]],
            [12, [
                [0, 3],
                [3, 6],
                [6, 9],
                [9, 11],
            ]],
        ];
    }

    protected function assertPairs(array $expectedPairs): void
    {
        $this->assertSame($expectedPairs, $this->pairs);
    }
}
