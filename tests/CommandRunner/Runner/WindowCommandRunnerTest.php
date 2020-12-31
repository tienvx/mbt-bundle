<?php

namespace Tienvx\Bundle\MbtBundle\Tests\CommandRunner\Runner;

use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverOptions;
use Facebook\WebDriver\WebDriverWindow;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\WindowCommandRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\Runner\WindowCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 */
class WindowCommandRunnerTest extends RunnerTestCase
{
    public function testOpen(): void
    {
        $command = new Command();
        $command->setCommand(WindowCommandRunner::OPEN);
        $command->setTarget('https://demo.sylius.com/en_US/');
        $this->driver->expects($this->once())->method('get')->with('https://demo.sylius.com/en_US/');
        $runner = new WindowCommandRunner();
        $runner->run($command, $this->driver);
    }

    public function testSetWindowSize(): void
    {
        $command = new Command();
        $command->setCommand(WindowCommandRunner::SET_WINDOW_SIZE);
        $command->setTarget('1280x800');
        $window = $this->createMock(WebDriverWindow::class);
        $window->expects($this->once())->method('setSize')->with($this->callback(function ($dimention) {
            return $dimention instanceof WebDriverDimension
                && 1280 === $dimention->getWidth()
                && 800 === $dimention->getHeight();
        }));
        $options = $this->createMock(WebDriverOptions::class);
        $options->expects($this->once())->method('window')->willReturn($window);
        $this->driver->expects($this->once())->method('manage')->willReturn($options);
        $runner = new WindowCommandRunner();
        $runner->run($command, $this->driver);
    }
}
