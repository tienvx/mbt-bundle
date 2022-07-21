<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Custom;

use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Tienvx\Bundle\MbtBundle\Command\Custom\UploadCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Custom\UploadCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Custom\AbstractCustomCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class UploadCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = 'File to upload';
    protected string $group = 'custom';
    protected string $uploadDir = __DIR__ . '/../../Fixtures';

    protected function createCommand(): UploadCommand
    {
        return new UploadCommand($this->uploadDir);
    }

    public function testRun(): void
    {
        $this->element = $this->createMock(RemoteWebElement::class);
        $this->element
            ->expects($this->once())
            ->method('setFileDetector')
            ->with($this->isInstanceOf(LocalFileDetector::class))
            ->willReturnSelf();
        $this->element
            ->expects($this->once())
            ->method('sendKeys')
            ->with($this->uploadDir . '/sub-directory/file.txt');
        $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'id' === $selector->getMechanism()
                    && 'file_input' === $selector->getValue();
            }))
            ->willReturn($this->element);
        $this->command->run('id=file_input', 'sub-directory/file.txt', $this->values, $this->driver);
    }

    public function targetProvider(): array
    {
        return [
            [null, false],
            ['', false],
            ['anything', false],
            ['css=#selector', true],
        ];
    }

    public function valueProvider(): array
    {
        return [
            [null, false],
            ['', false],
            ['anything', false],
            ['video.mp4', true],
        ];
    }
}
