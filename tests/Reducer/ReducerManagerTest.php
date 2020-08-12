<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\ReducerManager
 * @covers \Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager
 */
class ReducerManagerTest extends TestCase
{
    protected ReducerManager $reducerManager;
    protected ReducerInterface $reducer;
    protected ServiceLocator $locator;

    protected function setUp(): void
    {
        $this->reducer = $this->createMock(ReducerInterface::class);
        $this->locator = $this->createMock(ServiceLocator::class);
        $plugins = ['split', 'random'];
        $this->reducerManager = new ReducerManager($this->locator, $plugins);
    }

    public function testGet(): void
    {
        $this->locator->expects($this->once())->method('has')->with('random')->willReturn(true);
        $this->locator->expects($this->once())->method('get')->with('random')->willReturn($this->reducer);
        $this->assertSame($this->reducer, $this->reducerManager->get('random'));
    }

    public function testAll(): void
    {
        $this->assertSame(['split', 'random'], $this->reducerManager->all());
    }

    public function testHasSplit(): void
    {
        $this->locator->expects($this->once())->method('has')->with('split')->willReturn(true);
        $this->assertTrue($this->reducerManager->has('split'));
    }

    public function testHasRandom(): void
    {
        $this->locator->expects($this->once())->method('has')->with('random')->willReturn(true);
        $this->assertTrue($this->reducerManager->has('random'));
    }

    public function testDoesNotHaveOther(): void
    {
        $this->locator->expects($this->never())->method('has');
        $this->assertFalse($this->reducerManager->has('other'));
    }
}
