<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Channel;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Tienvx\Bundle\MbtBundle\Channel\ChannelManager;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Channel\ChannelManager
 * @covers \Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager
 */
class ChannelManagerTest extends TestCase
{
    protected ChannelManager $channelManager;
    protected ServiceLocator $locator;

    protected function setUp(): void
    {
        $this->locator = $this->createMock(ServiceLocator::class);
        $plugins = ['split', 'random'];
        $this->channelManager = new ChannelManager($this->locator, $plugins);
    }

    public function testDoesNotHaveOther(): void
    {
        $this->locator->expects($this->never())->method('has');
        $this->assertFalse($this->channelManager->has('other'));
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Channel "other" does not exist.');
        $this->channelManager->getChannel('other');
    }
}
