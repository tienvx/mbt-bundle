<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer\Split;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;
use Tienvx\Bundle\MbtBundle\Reducer\Split\SplitDispatcher;
use Tienvx\Bundle\MbtBundle\Reducer\Split\SplitHandler;
use Tienvx\Bundle\MbtBundle\Reducer\Split\SplitReducer;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\Split\SplitReducer
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\ReducerTemplate
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 */
class SplitReducerTest extends TestCase
{
    protected SplitDispatcher $dispatcher;
    protected SplitHandler $handler;
    protected SplitReducer $reducer;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(SplitDispatcher::class);
        $this->handler = $this->createMock(SplitHandler::class);
        $this->reducer = new SplitReducer($this->dispatcher, $this->handler);
    }

    public function testGetManager(): void
    {
        $this->assertSame(ReducerManager::class, SplitReducer::getManager());
    }

    public function testGetName(): void
    {
        $this->assertSame('split', SplitReducer::getName());
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
