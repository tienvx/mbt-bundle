<?php

namespace Tienvx\Bundle\MbtBundle\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
// use Symfony\Component\DependencyInjection\Loader\Configurator\ServiceConfigurator;
// use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginPass;
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
    // protected ContainerConfigurator|MockObject $container;
    protected DefinitionConfigurator|MockObject $definition;
    protected TienvxMbtBundle $bundle;

    protected function setUp(): void
    {
        $this->builder = new ContainerBuilder();
        // $this->container = $this->createMock(ContainerConfigurator::class);
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

    /*public function testLoadExtension(): void
    {
        $this->container->expects($this->once())->method('import')->with('../config/services.php');
        $services = $this->createMock(ServicesConfigurator::class);
        $selenoidHelper = $this->createMock(ServiceConfigurator::class);
        $customCommandRunner = $this->createMock(ServiceConfigurator::class);
        $services->expects($this->exactly(2))->method('get')->willReturnMap([
            [SelenoidHelperInterface::class, $selenoidHelper],
            [CustomCommandRunner::class, $customCommandRunner],
        ]);
        $selenoidHelper
            ->expects($this->once())
            ->method('call')
            ->with('setWebdriverUri', [static::CONFIG[TienvxMbtBundle::WEBDRIVER_URI]]);
        $customCommandRunner->expects($this->exactly(2))->method('call')->withConsecutive([
            ['setWebdriverUri', [static::CONFIG[TienvxMbtBundle::WEBDRIVER_URI]]],
            ['setUploadDir', [static::CONFIG[TienvxMbtBundle::UPLOAD_DIR]]],
        ]);
        $this->container->expects($this->exactly(2))->method('services')->willReturn($services);
        $this->bundle->loadExtension(static::CONFIG, $this->container, $this->builder);
    }*/
}
