<?php

namespace Tienvx\Bundle\MbtBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginPass;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Provider\Selenoid;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Plugin\Manager1;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Plugin\Manager2;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Plugin\Plugin11;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Plugin\Plugin12;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Plugin\Plugin21;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Plugin\Plugin22;

/**
 * @covers \Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginPass
 * @covers \Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager
 * @covers \Tienvx\Bundle\MbtBundle\Provider\AbstractProvider
 * @covers \Tienvx\Bundle\MbtBundle\Provider\ProviderManager
 * @covers \Tienvx\Bundle\MbtBundle\Provider\Selenoid
 */
class PluginPassTest extends TestCase
{
    protected ContainerBuilder $container;

    protected function setUp(): void
    {
        $container = new ContainerBuilder();

        $container->register(Manager1::class, Manager1::class);
        $container->register(Manager2::class, Manager2::class);
        $container->register(Plugin11::class, Plugin11::class)->addTag(PluginInterface::TAG);
        $container->register(Plugin12::class, Plugin12::class)->addTag(PluginInterface::TAG);
        $container->register(Plugin21::class, Plugin21::class)->addTag(PluginInterface::TAG);
        $container->register(Plugin22::class, Plugin22::class)->addTag(PluginInterface::TAG);

        $container->register(ProviderManager::class, ProviderManager::class);
        $container->register(Selenoid::class, Selenoid::class)->addTag(PluginInterface::TAG);

        $container->setParameter('%env(PROVIDER_NAME)%', 'selenoid');
        $container->setParameter('%env(SELENIUM_SERVER)%', 'http://localhost:4444');

        (new PluginPass())->process($container);

        $this->container = $container;
    }

    public function testProcessSupportedPlugins(): void
    {
        $this->assertSame(['plugin11'], $this->container->get(Manager1::class)->all());
        $this->assertTrue($this->container->get(Manager1::class)->has('plugin11'));
        $this->assertFalse($this->container->get(Manager1::class)->has('plugin12'));
        $this->assertInstanceOf(Plugin11::class, $this->container->get(Manager1::class)->get('plugin11'));
        $this->assertSame(['plugin21', 'plugin22'], $this->container->get(Manager2::class)->all());
        $this->assertTrue($this->container->get(Manager2::class)->has('plugin21'));
        $this->assertTrue($this->container->get(Manager2::class)->has('plugin22'));
        $this->assertInstanceOf(Plugin21::class, $this->container->get(Manager2::class)->get('plugin21'));
        $this->assertInstanceOf(Plugin22::class, $this->container->get(Manager2::class)->get('plugin22'));
    }

    public function testProcessNotSupportedPlugin(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Plugin "plugin12" does not exist.');
        $this->container->get(Manager1::class)->get('plugin12');
    }

    public function testParameters(): void
    {
        $this->assertSame(
            'http://localhost:4444/wd/hub',
            $this->container->get(Selenoid::class)->getSeleniumServerUrl()
        );
    }
}
