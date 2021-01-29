<?php

namespace Tienvx\Bundle\MbtBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Configuration;
use Tienvx\Bundle\MbtBundle\DependencyInjection\TienvxMbtExtension;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Service\Task\TaskHelperInterface;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Config;

/**
 * @covers \Tienvx\Bundle\MbtBundle\DependencyInjection\TienvxMbtExtension
 * @covers \Tienvx\Bundle\MbtBundle\DependencyInjection\Configuration
 */
class TienvxMbtExtensionTest extends TestCase
{
    /**
     * @dataProvider missingConfigProvider
     */
    public function testExceptionMissingSeleniumServer(string $missingConfig): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $loader = new TienvxMbtExtension();
        $config = Config::DEFAULT_CONFIG;
        unset($config[$missingConfig]);
        $loader->load([$config], new ContainerBuilder());
    }

    public function missingConfigProvider(): array
    {
        return [
            [Configuration::MAX_STEPS],
        ];
    }

    public function testUpdateServiceDefinitions(): void
    {
        $container = new ContainerBuilder();
        $loader = new TienvxMbtExtension();
        $config = Config::DEFAULT_CONFIG;
        $loader->load([$config], $container);
        $this->assertSame([
            ['setConfig', [$config[Configuration::PROVIDERS]]],
        ], $container->findDefinition(ProviderManager::class)->getMethodCalls());
        $this->assertSame([
            ['setMaxSteps', [$config[Configuration::MAX_STEPS]]],
        ], $container->findDefinition(TaskHelperInterface::class)->getMethodCalls());
    }

    public function testRegisterForAutoconfiguration(): void
    {
        $container = new ContainerBuilder();
        $loader = new TienvxMbtExtension();
        $config = Config::DEFAULT_CONFIG;
        $loader->load([$config], $container);
        $autoConfigured = $container->getAutoconfiguredInstanceof()[PluginInterface::class];
        $this->assertTrue($autoConfigured->hasTag(PluginInterface::TAG));
        $this->assertTrue($autoConfigured->isLazy());
    }
}
