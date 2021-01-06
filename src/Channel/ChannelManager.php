<?php

namespace Tienvx\Bundle\MbtBundle\Channel;

use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager;

class ChannelManager extends AbstractPluginManager implements ChannelManagerInterface
{
    public function getChannel(string $name): ChannelInterface
    {
        if ($this->has($name) && ($channel = $this->get($name)) && $channel instanceof ChannelInterface) {
            return $channel;
        }

        throw new UnexpectedValueException(sprintf('Channel "%s" does not exist.', $name));
    }
}
