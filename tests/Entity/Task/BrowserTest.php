<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity\Task;

use Tienvx\Bundle\MbtBundle\Entity\Task\Browser;
use Tienvx\Bundle\MbtBundle\Model\Task\BrowserInterface;
use Tienvx\Bundle\MbtBundle\Tests\Model\Task\BrowserTest as BrowserModelTest;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task\Browser
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task\Browser
 */
class BrowserTest extends BrowserModelTest
{
    protected function createBrowser(): BrowserInterface
    {
        return new Browser();
    }
}
