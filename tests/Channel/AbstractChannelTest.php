<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Channel;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Channel\AbstractChannel;
use Tienvx\Bundle\MbtBundle\Channel\ChannelManager;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Channel\AbstractChannel
 */
class AbstractChannelTest extends TestCase
{
    public function testGetManager(): void
    {
        $this->assertSame(ChannelManager::class, AbstractChannel::getManager());
    }
}
