<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Runner;

use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\CustomCommandRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\CustomCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandRunner
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 */
class CustomCommandRunnerTest extends RunnerTestCase
{
    protected function createRunner(): CommandRunner
    {
        return new CustomCommandRunner('/path/to/upload-directory');
    }

    public function testUpload(): void
    {
        $command = new Command();
        $command->setCommand(CustomCommandRunner::UPLOAD);
        $command->setTarget('id=file_input');
        $command->setValue('sub-directory/file.txt');
        $element = $this->createMock(RemoteWebElement::class);
        $element
            ->expects($this->once())
            ->method('setFileDetector')
            ->with($this->isInstanceOf(LocalFileDetector::class))
            ->willReturnSelf();
        $element
            ->expects($this->once())
            ->method('sendKeys')
            ->with('/path/to/upload-directory/sub-directory/file.txt');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'file_input' === $selector->getValue();
        }))->willReturn($element);
        $this->runner->run($command, $this->values, $this->driver);
    }

    public function targetProvider(): array
    {
        return [
            [CustomCommandRunner::UPLOAD, null, false],
            [CustomCommandRunner::UPLOAD, 'anything', false],
            [CustomCommandRunner::UPLOAD, 'xpath=//path/to/element', true],
        ];
    }
}
