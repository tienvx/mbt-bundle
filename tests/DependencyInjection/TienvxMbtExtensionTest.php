<?php

namespace Tienvx\Bundle\MbtBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tienvx\Bundle\MbtBundle\Command\CommandRunnerInterface;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Configuration;
use Tienvx\Bundle\MbtBundle\DependencyInjection\TienvxMbtExtension;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelperInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\DependencyInjection\TienvxMbtExtension
 * @covers \Tienvx\Bundle\MbtBundle\DependencyInjection\Configuration
 */
class TienvxMbtExtensionTest extends TestCase
{
    protected const CONFIGS = [[
        Configuration::WEBDRIVER_URI => 'http://localhost:4444',
    ]];

    protected ContainerBuilder $container;
    protected TienvxMbtExtension $loader;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->loader = new TienvxMbtExtension();
    }

    public function testExceptionMissingSeleniumServer(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $loader = new TienvxMbtExtension();
        $loader->load([], new ContainerBuilder());
    }

    public function testUpdateServiceDefinitions(): void
    {
        $this->loader->load(static::CONFIGS, $this->container);
        $this->assertSame([
            ['setWebdriverUri', [static::CONFIGS[0][Configuration::WEBDRIVER_URI]]],
        ], $this->container->findDefinition(SelenoidHelperInterface::class)->getMethodCalls());
    }

    public function testRegisterForAutoconfiguration(): void
    {
        $this->loader->load(static::CONFIGS, $this->container);
        $interfaces = [PluginInterface::class, CommandRunnerInterface::class];
        foreach ($interfaces as $interface) {
            $autoConfigured = $this->container->getAutoconfiguredInstanceof()[$interface];
            $this->assertTrue($autoConfigured->hasTag($interface::TAG));
            $this->assertTrue($autoConfigured->isLazy());
        }
    }
}
