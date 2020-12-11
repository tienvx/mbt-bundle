<?php

namespace Tienvx\Bundle\MbtBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tienvx\Bundle\MbtBundle\DependencyInjection\TienvxMbtExtension;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Service\BugHelperInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\DependencyInjection\TienvxMbtExtension
 * @covers \Tienvx\Bundle\MbtBundle\DependencyInjection\Configuration
 */
class TienvxMbtExtensionTest extends TestCase
{
    /**
     * @dataProvider missingConfigProvider
     */
    public function testExceptionMissingSeleniumServer(string $config): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $loader = new TienvxMbtExtension();
        $config = $this->getDefaultConfig();
        unset($config['selenium_server']);
        $loader->load([$config], new ContainerBuilder());
    }

    public function missingConfigProvider(): array
    {
        return [
            ['selenium_server'],
            ['admin_url'],
            ['provider_name'],
        ];
    }

    public function testUpdateServiceDefinitions(): void
    {
        $container = new ContainerBuilder();
        $loader = new TienvxMbtExtension();
        $config = $this->getDefaultConfig();
        $loader->load([$config], $container);
        $this->assertSame([
            ['setSeleniumServer', ['http://localhost:4444']],
            ['setProviderName', ['selenoid']],
        ], $container->findDefinition(ProviderManager::class)->getMethodCalls());
        $this->assertSame([
            ['setAdminUrl', ['http://localhost']],
        ], $container->findDefinition(BugHelperInterface::class)->getMethodCalls());
    }

    protected function getDefaultConfig(): array
    {
        return [
            'selenium_server' => 'http://localhost:4444',
            'admin_url' => 'http://localhost',
            'provider_name' => 'selenoid',
        ];
    }

    public function testRegisterForAutoconfiguration(): void
    {
        $container = new ContainerBuilder();
        $loader = new TienvxMbtExtension();
        $config = $this->getDefaultConfig();
        $loader->load([$config], $container);
        $autoConfigured = $container->getAutoconfiguredInstanceof()[PluginInterface::class];
        $this->assertTrue($autoConfigured->hasTag(PluginInterface::TAG));
        $this->assertTrue($autoConfigured->isLazy());
    }
}
