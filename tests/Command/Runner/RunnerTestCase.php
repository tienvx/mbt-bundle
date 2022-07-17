<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverElement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

abstract class RunnerTestCase extends TestCase
{
    protected RemoteWebDriver|MockObject $driver;
    protected CommandRunner $runner;
    protected ValuesInterface|MockObject $values;
    protected WebDriverElement|MockObject $element;

    protected function setUp(): void
    {
        $this->driver = $this->createMock(RemoteWebDriver::class);
        $this->runner = $this->createRunner();
        $this->values = $this->createMock(ValuesInterface::class);
        $this->element = $this->createMock(WebDriverElement::class);
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

    public function testGetCommandsRequireTarget(): void
    {
        $this->assertSame($this->commandsRequireTarget(), $this->runner->getCommandsRequireTarget());
    }

    abstract public function commandsRequireTarget(): array;

    public function testGetCommandsRequireValue(): void
    {
        $this->assertSame($this->commandsRequireValue(), $this->runner->getCommandsRequireValue());
    }

    abstract public function commandsRequireValue(): array;
}
