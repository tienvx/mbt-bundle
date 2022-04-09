<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer;

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

    protected function getPluginManagerClass(): string
    {
        return ReducerManager::class;
    }

    protected function getPluginInterface(): string
    {
        return ReducerInterface::class;
    }

    protected function getInvalidPluginExceptionMessage(string $plugin): string
    {
        return sprintf('Reducer "%s" does not exist.', $plugin);
    }
}
