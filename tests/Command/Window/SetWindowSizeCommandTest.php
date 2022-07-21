<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Window;

use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverOptions;
use Facebook\WebDriver\WebDriverWindow;
use Tienvx\Bundle\MbtBundle\Command\Window\SetWindowSizeCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Window\SetWindowSizeCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Window\AbstractWindowCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class SetWindowSizeCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = 'Dimension e.g. 1280x720';
    protected string $valueHelper = '';
    protected string $group = 'window';

    protected function createCommand(): SetWindowSizeCommand
    {
        return new SetWindowSizeCommand();
    }

    public function testRun(): void
    {
        $window = $this->createMock(WebDriverWindow::class);
        $window->expects($this->once())->method('setSize')->with($this->callback(function ($dimention) {
            return $dimention instanceof WebDriverDimension
                && 1280 === $dimention->getWidth()
                && 800 === $dimention->getHeight();
        }));
        $options = $this->createMock(WebDriverOptions::class);
        $options->expects($this->once())->method('window')->willReturn($window);
        $this->driver->expects($this->once())->method('manage')->willReturn($options);
        $this->command->run('1280x800', null, $this->values, $this->driver);
    }

    public function targetProvider(): array
    {
        return [
            [null, false],
            ['', false],
            ['anything', false],
            ['1+2', false],
            ['720p', false],
            ['1280x', false],
            ['x720', false],
            ['1280x720', true],
        ];
    }

    public function valueProvider(): array
    {
        return [
            [null, true],
            ['', true],
            ['anything', true],
        ];
    }
}
