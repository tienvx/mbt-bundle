<?php

namespace Tienvx\Bundle\MbtBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tienvx\Bundle\MbtBundle\DependencyInjection\TienvxMbtExtension;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\DependencyInjection\TienvxMbtExtension
 * @covers \Tienvx\Bundle\MbtBundle\DependencyInjection\Configuration
 */
class TienvxMbtExtensionTest extends TestCase
{
    public function testRegisterForAutoconfiguration(): void
    {
        $container = new ContainerBuilder();
        $loader = new TienvxMbtExtension();
        $loader->load([], $container);
        $autoConfigured = $container->getAutoconfiguredInstanceof()[PluginInterface::class];
        $this->assertTrue($autoConfigured->hasTag(PluginInterface::TAG));
        $this->assertTrue($autoConfigured->isLazy());
    }
}
