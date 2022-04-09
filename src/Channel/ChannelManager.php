<?php

namespace Tienvx\Bundle\MbtBundle\Channel;

use Tienvx\Bundle\MbtBundle\Plugin\PluginManager;

class ChannelManager extends PluginManager implements ChannelManagerInterface
{
    public function getChannel(string $name): ChannelInterface
    {
        return parent::get($name);
    }

    protected function getPluginInterface(): string
    {
        return ChannelInterface::class;
    }

    protected function getInvalidPluginExceptionMessage(string $name): string
    {
        return sprintf('Channel "%s" does not exist.', $name);
    }
}
