<?php

namespace Tienvx\Bundle\MbtBundle\Tests\CommandRunner\Runner;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\Runner\MouseCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 */
class MouseCommandRunnerTest extends RunnerTestCase
{
    public function testClick(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::CLICK);
        $command->setTarget('id=add-to-cart');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('click');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'add-to-cart' === $selector->getValue();
        }))->willReturn($element);
        $runner = new MouseCommandRunner();
        $runner->run($command, $this->driver);
    }
}
