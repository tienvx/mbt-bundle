<?php

namespace Tienvx\Bundle\MbtBundle\Tests\CommandRunner\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

abstract class RunnerTestCase extends TestCase
{
    protected RemoteWebDriver $driver;
    protected CommandRunner $runner;

    protected function setUp(): void
    {
        $this->driver = $this->createMock(RemoteWebDriver::class);
        $this->runner = $this->createRunner();
    }

    abstract protected function createRunner(): CommandRunner;

    /**
     * @dataProvider supportsCommandProvider
     */
    public function testSupports(string $commandAsString, bool $supports): void
    {
        $command = new Command();
        $command->setCommand($commandAsString);
        $this->assertSame($supports, $this->runner->supports($command));
    }

    public function supportsCommandProvider(): array
    {
        return [];
    }
}
