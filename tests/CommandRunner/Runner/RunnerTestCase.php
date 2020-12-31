<?php

namespace Tienvx\Bundle\MbtBundle\Tests\CommandRunner\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;

abstract class RunnerTestCase extends TestCase
{
    protected RemoteWebDriver $driver;

    protected function setUp(): void
    {
        $this->driver = $this->createMock(RemoteWebDriver::class);
    }
}
