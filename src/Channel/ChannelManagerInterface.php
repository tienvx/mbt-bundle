<?php

namespace Tienvx\Bundle\MbtBundle\Channel;

use Tienvx\Bundle\MbtBundle\Plugin\PluginManagerInterface;

interface ChannelManagerInterface extends PluginManagerInterface
{
    public function getChannel(string $name): ChannelInterface;
}
