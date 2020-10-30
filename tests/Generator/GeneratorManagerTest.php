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
        $plugins = ['random', 'all-places', 'all-transitions'];
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
        $this->assertSame(['random', 'all-places', 'all-transitions'], $this->generatorManager->all());
    }

    public function testHasSelenium()
    {
        $this->locator->expects($this->once())->method('has')->with('random')->willReturn(true);
        $this->assertTrue($this->generatorManager->has('random'));
    }

    public function testHasAllPlaces()
    {
        $this->locator->expects($this->once())->method('has')->with('all-places')->willReturn(true);
        $this->assertTrue($this->generatorManager->has('all-places'));
    }

    public function testHasAllTransitions()
    {
        $this->locator->expects($this->once())->method('has')->with('all-transitions')->willReturn(true);
        $this->assertTrue($this->generatorManager->has('all-transitions'));
    }

    public function testDoesNotHaveOther()
    {
        $this->locator->expects($this->never())->method('has');
        $this->assertFalse($this->generatorManager->has('other'));
    }
}
