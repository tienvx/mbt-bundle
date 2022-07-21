<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverElement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Command\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

abstract class CommandTestCase extends TestCase
{
    protected RemoteWebDriver|MockObject $driver;
    protected CommandInterface $command;
    protected ValuesInterface|MockObject $values;
    protected WebDriverElement|MockObject $element;
    protected bool $isTargetRequired = false;
    protected bool $isValueRequired = false;
    protected string $targetHelper = '';
    protected string $valueHelper = '';
    protected string $group = '';

    protected function setUp(): void
    {
        $this->driver = $this->createMock(RemoteWebDriver::class);
        $this->command = $this->createCommand();
        $this->values = $this->createMock(ValuesInterface::class);
        $this->element = $this->createMock(WebDriverElement::class);
    }

    abstract protected function createCommand(): CommandInterface;

    /**
     * @dataProvider targetProvider
     */
    public function testValidateTarget(?string $target, bool $valid): void
    {
        $this->assertSame($valid, get_class($this->command)::validateTarget($target));
    }

    abstract public function targetProvider(): array;

    /**
     * @dataProvider valueProvider
     */
    public function testValidateValue(?string $value, bool $valid): void
    {
        $this->assertSame($valid, $this->command->validateValue($value));
    }

    abstract public function valueProvider(): array;

    public function testIsTargetRequired(): void
    {
        $this->assertSame($this->isTargetRequired, get_class($this->command)::isTargetRequired());
    }

    public function testIsValueRequired(): void
    {
        $this->assertSame($this->isValueRequired, get_class($this->command)::isValueRequired());
    }

    public function testGetTargetHelper(): void
    {
        $this->assertSame($this->targetHelper, get_class($this->command)::getTargetHelper());
    }

    public function testGetValueHelper(): void
    {
        $this->assertSame($this->valueHelper, get_class($this->command)::getValueHelper());
    }

    public function testGetGroup(): void
    {
        $this->assertSame($this->group, get_class($this->command)::getGroup());
    }
}
