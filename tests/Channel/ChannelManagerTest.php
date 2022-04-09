<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Channel;

use Tienvx\Bundle\MbtBundle\Channel\ChannelInterface;
use Tienvx\Bundle\MbtBundle\Channel\ChannelManager;
use Tienvx\Bundle\MbtBundle\Tests\Plugin\PluginManagerTest;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Channel\ChannelManager
 *
 * @uses \Tienvx\Bundle\MbtBundle\Plugin\PluginManager
 */
class ChannelManagerTest extends PluginManagerTest
{
    protected array $plugins = ['email', 'slack/chat'];
    protected string $getMethod = 'getChannel';

    protected function getPluginManagerClass(): string
    {
        return ChannelManager::class;
    }

    protected function getPluginInterface(): string
    {
        return ChannelInterface::class;
    }

    protected function getInvalidPluginExceptionMessage(string $plugin): string
    {
        return sprintf('Channel "%s" does not exist.', $plugin);
    }
}
