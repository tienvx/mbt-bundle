<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Plugin;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;
use Tienvx\Bundle\MbtBundle\Plugin\PluginManager;
use Tienvx\Bundle\MbtBundle\Plugin\PluginManagerInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Plugin\PluginManager
 */
class PluginManagerTest extends TestCase
{
    protected PluginManagerInterface $pluginManager;
    protected PluginInterface $plugin;
    protected ServiceLocator|MockObject $locator;
    protected array $plugins = ['plugin1', 'plugin2'];
    protected string $getMethod = 'get';

    protected function setUp(): void
    {
        $this->plugin = $this->createPlugin();
        $this->locator = $this->createMock(ServiceLocator::class);
        $this->pluginManager = $this->createPluginManager();
    }

    /**
     * @dataProvider pluginProvider
     */
    public function testGet(string $plugin): void
    {
        $this->locator->expects($this->once())->method('has')->with($plugin)->willReturn(true);
        $this->locator->expects($this->once())->method('get')->with($plugin)->willReturn($this->plugin);
        $this->assertSame($this->plugin, $this->pluginManager->{$this->getMethod}($plugin));
    }

    public function testAll(): void
    {
        $this->assertSame($this->plugins, $this->pluginManager->all());
    }

    /**
     * @dataProvider pluginProvider
     */
    public function testHas(string $plugin): void
    {
        $this->locator->expects($this->once())->method('has')->with($plugin)->willReturn(true);
        $this->assertTrue($this->pluginManager->has($plugin));
    }

    public function testInvalidPlugin(): void
    {
        $plugin = 'invalid';
        $this->assertFalse(in_array($plugin, $this->plugins));
        $this->locator->expects($this->never())->method('has');
        $this->assertFalse($this->pluginManager->has($plugin));
        $this->expectExceptionObject(new UnexpectedValueException($this->getInvalidPluginExceptionMessage($plugin)));
        $this->pluginManager->get($plugin);
    }

    protected function createPluginManager(): PluginManagerInterface
    {
        return new PluginManager($this->locator, $this->plugins);
    }

    protected function createPlugin(): PluginInterface
    {
        return $this->createMock(PluginInterface::class);
    }

    protected function getInvalidPluginExceptionMessage(string $plugin): string
    {
        return sprintf('Plugin "%s" does not exist.', $plugin);
    }

    public function pluginProvider(): array
    {
        return [
            $this->plugins,
        ];
    }
}
