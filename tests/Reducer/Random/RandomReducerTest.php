<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer\Random;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Reducer\Random\RandomDispatcher;
use Tienvx\Bundle\MbtBundle\Reducer\Random\RandomHandler;
use Tienvx\Bundle\MbtBundle\Reducer\Random\RandomReducer;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\Random\RandomReducer
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\ReducerTemplate
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 */
class RandomReducerTest extends TestCase
{
    protected RandomDispatcher $dispatcher;
    protected RandomHandler $handler;
    protected RandomReducer $reducer;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(RandomDispatcher::class);
        $this->handler = $this->createMock(RandomHandler::class);
        $this->reducer = new RandomReducer($this->dispatcher, $this->handler);
    }

    public function testGetManager(): void
    {
        $this->assertSame(ReducerManager::class, RandomReducer::getManager());
    }

    public function testGetName(): void
    {
        $this->assertSame('random', RandomReducer::getName());
    }

    public function testDispatch(): void
    {
        $bug = new Bug();
        $this->dispatcher->expects($this->once())->method('dispatch')->with($bug)->willReturn(123);
        $this->assertSame(123, $this->reducer->dispatch($bug));
    }

    public function testHandle(): void
    {
        $bug = new Bug();
        $this->handler->expects($this->once())->method('handle')->with($bug, 12, 23);
        $this->reducer->handle($bug, 12, 23);
    }
}
