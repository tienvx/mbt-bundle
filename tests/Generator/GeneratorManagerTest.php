<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Generator;

use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;
use Tienvx\Bundle\MbtBundle\Plugin\PluginManagerInterface;
use Tienvx\Bundle\MbtBundle\Tests\Plugin\PluginManagerTest;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Generator\GeneratorManager
 *
 * @uses \Tienvx\Bundle\MbtBundle\Plugin\PluginManager
 */
class GeneratorManagerTest extends PluginManagerTest
{
    protected array $plugins = ['random'];
    protected string $getMethod = 'getGenerator';

    protected function createPluginManager(): PluginManagerInterface
    {
        return new GeneratorManager($this->locator, $this->plugins);
    }

    protected function createPlugin(): PluginInterface
    {
        return $this->createMock(GeneratorInterface::class);
    }

    protected function getInvalidPluginExceptionMessage(string $plugin): string
    {
        return sprintf('Generator "%s" does not exist.', $plugin);
    }
}
