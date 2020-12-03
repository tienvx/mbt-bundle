<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Generator;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Generator\GeneratorManager
 * @covers \Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager
 */
class GeneratorManagerTest extends TestCase
{
    protected GeneratorManager $generatorManager;
    protected GeneratorInterface $generator;
    protected ServiceLocator $locator;

    protected function setUp(): void
    {
        $this->generator = $this->createMock(GeneratorInterface::class);
        $this->locator = $this->createMock(ServiceLocator::class);
        $plugins = ['random'];
        $this->generatorManager = new GeneratorManager($this->locator, $plugins);
    }

    public function testGet()
    {
        $this->locator->expects($this->once())->method('has')->with('random')->willReturn(true);
        $this->locator->expects($this->once())->method('get')->with('random')->willReturn($this->generator);
        $this->assertSame($this->generator, $this->generatorManager->get('random'));
    }

    public function testAll()
    {
        $this->assertSame(['random'], $this->generatorManager->all());
    }

    public function testHasRandom()
    {
        $this->locator->expects($this->once())->method('has')->with('random')->willReturn(true);
        $this->assertTrue($this->generatorManager->has('random'));
    }

    public function testDoesNotHaveOther()
    {
        $this->locator->expects($this->never())->method('has');
        $this->assertFalse($this->generatorManager->has('other'));
    }
}
