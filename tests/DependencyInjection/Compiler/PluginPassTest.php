<?php

namespace Tienvx\Bundle\MbtBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginPass;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Plugin\Manager1;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Plugin\Manager2;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Plugin\Plugin11;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Plugin\Plugin12;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Plugin\Plugin21;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Plugin\Plugin22;

/**
 * @covers \Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginPass
 * @covers \Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager
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

        (new PluginPass())->process($container);

        $this->container = $container;
    }

    public function testProcessSupportedPlugins(): void
    {
        $manager1 = $this->container->get(Manager1::class);
        $this->assertSame(['plugin11'], $manager1->all());
        $this->assertTrue($manager1->has('plugin11'));
        $this->assertFalse($manager1->has('plugin12'));
        $this->assertInstanceOf(Plugin11::class, $manager1->get('plugin11'));

        $manager2 = $this->container->get(Manager2::class);
        $this->assertSame(['plugin21', 'plugin22'], $manager2->all());
        $this->assertTrue($manager2->has('plugin21'));
        $this->assertTrue($manager2->has('plugin22'));
        $this->assertInstanceOf(Plugin21::class, $manager2->get('plugin21'));
        $this->assertInstanceOf(Plugin22::class, $manager2->get('plugin22'));
    }

    public function testProcessNotSupportedPlugin(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Plugin "plugin12" does not exist.');
        $this->container->get(Manager1::class)->get('plugin12');
    }
}
