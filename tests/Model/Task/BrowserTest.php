<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Task;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Model\Task\Browser;
use Tienvx\Bundle\MbtBundle\Model\Task\BrowserInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\Browser
 */
class BrowserTest extends TestCase
{
    protected BrowserInterface $browser;

    protected function setUp(): void
    {
        $this->browser = $this->createBrowser();
    }

    public function testName(): void
    {
        $this->browser->setName('firefox');
        $this->assertSame('firefox', $this->browser->getName());
    }

    public function testVersion(): void
    {
        $this->browser->setVersion('99.0');
        $this->assertSame('99.0', $this->browser->getVersion());
    }

    protected function createBrowser(): BrowserInterface
    {
        return new Browser();
    }
}
