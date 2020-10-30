<?php

namespace Tienvx\Bundle\MbtBundle\Channel;

use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager;

class ChannelManager extends AbstractPluginManager
{
    /**
     * @throws ExceptionInterface
     */
    public function get(string $name): ChannelInterface
    {
        $channel = $this->locator->has($name) ? $this->locator->get($name) : null;
        if ($channel instanceof ChannelInterface) {
            return $channel;
        }

        throw new UnexpectedValueException(sprintf('Channel "%s" does not exist.', $name));
    }
}
