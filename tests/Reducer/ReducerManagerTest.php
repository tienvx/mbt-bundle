<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer;

use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;
use Tienvx\Bundle\MbtBundle\Plugin\PluginManagerInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;
use Tienvx\Bundle\MbtBundle\Tests\Plugin\PluginManagerTest;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\ReducerManager
 *
 * @uses \Tienvx\Bundle\MbtBundle\Plugin\PluginManager
 */
class ReducerManagerTest extends PluginManagerTest
{
    protected array $plugins = ['split', 'random'];
    protected string $getMethod = 'getReducer';

    protected function createPluginManager(): PluginManagerInterface
    {
        return new ReducerManager($this->locator, $this->plugins);
    }

    protected function createPlugin(): PluginInterface
    {
        return $this->createMock(ReducerInterface::class);
    }

    protected function getInvalidPluginExceptionMessage(string $plugin): string
    {
        return sprintf('Reducer "%s" does not exist.', $plugin);
    }
}
