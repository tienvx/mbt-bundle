<?php

namespace Tienvx\Bundle\MbtBundle\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Tienvx\Bundle\MbtBundle\Command\CommandManager;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginPass;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelper;
use Tienvx\Bundle\MbtBundle\TienvxMbtBundle;

/**
 * @covers \Tienvx\Bundle\MbtBundle\TienvxMbtBundle
 */
class TienvxMbtBundleTest extends TestCase
{
    protected const CONFIG = [
        TienvxMbtBundle::WEBDRIVER_URI => 'http://localhost:4444',
        TienvxMbtBundle::UPLOAD_DIR => '/path/to/var/uploads',
    ];

    protected ContainerBuilder $builder;
    protected DefinitionConfigurator|MockObject $definition;
    protected TienvxMbtBundle $bundle;

    protected function setUp(): void
    {
        $this->builder = new ContainerBuilder();
        $this->definition = $this->createMock(DefinitionConfigurator::class);
        $this->bundle = new TienvxMbtBundle();
    }

    public function testBuild(): void
    {
        $oldPasses = $this->builder->getCompiler()->getPassConfig()->getBeforeOptimizationPasses();
        $this->bundle->build($this->builder);
        $newPasses = $this->builder->getCompiler()->getPassConfig()->getBeforeOptimizationPasses();
        $this->assertSame(1, count($newPasses) - count($oldPasses));
        $hasPass = false;
        foreach ($newPasses as $pass) {
            if ($pass instanceof PluginPass) {
                $hasPass = true;
            }
        }
        $this->assertTrue($hasPass);
    }

    public function testConfigure(): void
    {
        $this->definition->expects($this->once())->method('import')->with('../config/definition.php');
        $this->bundle->configure($this->definition);
    }

    public function testLoadExtension(): void
    {
        $instanceof = [];
        $container = new ContainerConfigurator(
            $this->builder,
            new PhpFileLoader($this->builder, new FileLocator(\dirname(__DIR__) . '/config')),
            $instanceof,
            '',
            ''
        );
        $this->bundle->loadExtension(static::CONFIG, $container, $this->builder);
        $this->assertSame([
            ['setWebdriverUri', [static::CONFIG[TienvxMbtBundle::WEBDRIVER_URI]]],
        ], $this->builder->findDefinition(SelenoidHelper::class)->getMethodCalls());
        $this->assertSame([
            ['setUploadDir', [static::CONFIG[TienvxMbtBundle::UPLOAD_DIR]]],
        ], $this->builder->findDefinition(CommandManager::class)->getMethodCalls());
        $autoConfigured = $this->builder->getAutoconfiguredInstanceof()[PluginInterface::class];
        $this->assertTrue($autoConfigured->hasTag(PluginInterface::TAG));
        $this->assertTrue($autoConfigured->isLazy());
    }
}
