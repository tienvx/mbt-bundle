<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

abstract class RunnerTestCase extends TestCase
{
    protected RemoteWebDriver $driver;
    protected CommandRunner $runner;
    protected ColorInterface $color;

    protected function setUp(): void
    {
        $this->driver = $this->createMock(RemoteWebDriver::class);
        $this->runner = $this->createRunner();
        $this->color = $this->createMock(ColorInterface::class);
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
        return [
            ...array_map(fn ($command) => [$command, true], $this->createRunner()->getAllCommands()),
            ['invalidCommand', false],
        ];
    }

    /**
     * @dataProvider targetProvider
     */
    public function testValidateTarget(string $commandString, $target, bool $valid): void
    {
        $command = new Command();
        $command->setCommand($commandString);
        $command->setTarget($target);
        $this->assertSame($valid, $this->runner->validateTarget($command));
    }

    abstract public function targetProvider(): array;
}
