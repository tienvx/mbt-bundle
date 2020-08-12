<?php

namespace Tienvx\Bundle\MbtBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tienvx\Bundle\MbtBundle\DependencyInjection\TienvxMbtExtension;
use Tienvx\Bundle\MbtBundle\Service\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\SeleniumInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\DependencyInjection\TienvxMbtExtension
 * @covers \Tienvx\Bundle\MbtBundle\DependencyInjection\Configuration
 */
class TienvxMbtExtensionTest extends TestCase
{
    public function testExceptionMissingSeleniumDsn(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $loader = new TienvxMbtExtension();
        $config = $this->getDefaultConfig();
        unset($config['selenium_dsn']);
        $loader->load([$config], new ContainerBuilder());
    }

    public function testExceptionMissingBugUrl(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $loader = new TienvxMbtExtension();
        $config = $this->getDefaultConfig();
        unset($config['bug_url']);
        $loader->load([$config], new ContainerBuilder());
    }

    public function testUpdateServiceDefinitions(): void
    {
        $container = new ContainerBuilder();
        $loader = new TienvxMbtExtension();
        $config = $this->getDefaultConfig();
        $loader->load([$config], $container);
        $this->assertSame([
            ['setDsn', ['http://localhost:4444/wd/hub']],
        ], $container->findDefinition(SeleniumInterface::class)->getMethodCalls());
        $this->assertSame([
            ['setBugUrl', ['http://localhost/bug/%d']],
        ], $container->findDefinition(BugHelperInterface::class)->getMethodCalls());
    }

    protected function getDefaultConfig(): array
    {
        return [
            'selenium_dsn' => 'http://localhost:4444/wd/hub',
            'bug_url' => 'http://localhost/bug/%d',
        ];
    }
}
