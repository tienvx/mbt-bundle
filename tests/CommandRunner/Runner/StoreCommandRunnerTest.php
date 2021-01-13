<?php

namespace Tienvx\Bundle\MbtBundle\Tests\CommandRunner\Runner;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\StoreCommandRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\StoreCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 */
class StoreCommandRunnerTest extends RunnerTestCase
{
    protected function createRunner(): CommandRunner
    {
        return new StoreCommandRunner();
    }

    public function testStore(): void
    {
        $command = new Command();
        $command->setCommand(StoreCommandRunner::STORE);
        $command->setTarget('1');
        $command->setValue('count');
        $this->color->expects($this->once())->method('setValue')->with('count', '1');
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testStoreAttribute(): void
    {
        $command = new Command();
        $command->setCommand(StoreCommandRunner::STORE_ATTRIBUTE);
        $command->setTarget('css=.readmore@href');
        $command->setValue('readmoreLink');
        $this->color->expects($this->once())->method('setValue')->with('readmoreLink', 'http://example.com');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('getAttribute')->with('href')->willReturn('http://example.com');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.readmore' === $selector->getValue();
        }))->willReturn($element);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testStoreElementCount(): void
    {
        $command = new Command();
        $command->setCommand(StoreCommandRunner::STORE_ELEMENT_COUNT);
        $command->setTarget('css=.item');
        $command->setValue('itemCount');
        $this->color->expects($this->once())->method('setValue')->with('itemCount', 2);
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElements')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.item' === $selector->getValue();
        }))->willReturn([$element, $element]);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testStoreJson(): void
    {
        $command = new Command();
        $command->setCommand(StoreCommandRunner::STORE_JSON);
        $command->setTarget('{ "items": [1, 2, 3] }');
        $command->setValue('json');
        $this->color->expects($this->once())->method('setValue')->with(
            'json',
            $this->callback(fn ($object) => $object instanceof \stdClass && $object->items === [1, 2, 3])
        );
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testStoreText(): void
    {
        $command = new Command();
        $command->setCommand(StoreCommandRunner::STORE_TEXT);
        $command->setTarget('css=.head-line');
        $command->setValue('headLine');
        $this->color->expects($this->once())->method('setValue')->with('headLine', 'Welcome to our site');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('getText')->willReturn('Welcome to our site');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.head-line' === $selector->getValue();
        }))->willReturn($element);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testStoreTitle(): void
    {
        $command = new Command();
        $command->setCommand(StoreCommandRunner::STORE_TITLE);
        $command->setTarget('title');
        $this->color->expects($this->once())->method('setValue')->with('title', 'Welcome');
        $this->driver->expects($this->once())->method('getTitle')->willReturn('Welcome');
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testStoreValue(): void
    {
        $command = new Command();
        $command->setCommand(StoreCommandRunner::STORE_VALUE);
        $command->setTarget('css=.age');
        $command->setValue('age');
        $this->color->expects($this->once())->method('setValue')->with('age', 23);
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('getAttribute')->with('value')->willReturn(23);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.age' === $selector->getValue();
        }))->willReturn($element);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testStoreWindowHandle(): void
    {
        $command = new Command();
        $command->setCommand(StoreCommandRunner::STORE_WINDOW_HANDLE);
        $command->setTarget('windowHandle');
        $this->color->expects($this->once())->method('setValue')->with('windowHandle', 'window-123');
        $this->driver->expects($this->once())->method('getWindowHandle')->willReturn('window-123');
        $this->runner->run($command, $this->color, $this->driver);
    }
}
