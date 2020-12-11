<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Tienvx\Bundle\MbtBundle\Provider\ProviderInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Provider\ProviderManager
 * @covers \Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager
 */
class ProviderManagerTest extends TestCase
{
    protected ProviderManager $providerManager;
    protected ProviderInterface $provider;
    protected ServiceLocator $locator;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(ProviderInterface::class);
        $this->locator = $this->createMock(ServiceLocator::class);
        $plugins = ['selenoid'];
        $this->providerManager = new ProviderManager($this->locator, $plugins);
    }

    public function testGet()
    {
        $this->locator->expects($this->once())->method('has')->with('selenoid')->willReturn(true);
        $this->locator->expects($this->once())->method('get')->with('selenoid')->willReturn($this->provider);
        $this->assertSame($this->provider, $this->providerManager->get('selenoid'));
    }

    public function testAll()
    {
        $this->assertSame(['selenoid'], $this->providerManager->all());
    }

    public function testHasSelenoid()
    {
        $this->locator->expects($this->once())->method('has')->with('selenoid')->willReturn(true);
        $this->assertTrue($this->providerManager->has('selenoid'));
    }

    public function testGetCurrentProvider()
    {
        $this->providerManager->setProviderName('selenoid');
        $this->locator->expects($this->once())->method('has')->with('selenoid')->willReturn(true);
        $this->locator->expects($this->once())->method('get')->with('selenoid')->willReturn($this->provider);
        $this->assertInstanceOf(ProviderInterface::class, $this->providerManager->getProvider());
    }

    public function testDoesNotHaveOther()
    {
        $this->locator->expects($this->never())->method('has');
        $this->assertFalse($this->providerManager->has('other'));
    }
}
