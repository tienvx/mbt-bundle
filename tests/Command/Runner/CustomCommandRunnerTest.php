<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Runner;

use Exception;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tienvx\Bundle\MbtBundle\Command\Runner\CustomCommandRunner;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Exception\HttpClientException;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\CustomCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandRunner
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 */
class CustomCommandRunnerTest extends RunnerTestCase
{
    protected string $webdriverUri = 'http://localhost:4444';
    protected string $uploadDir = '/path/to/upload-directory';
    protected string $sessionId = 'abc123';
    protected HttpClientInterface|MockObject $httpClient;

    protected function createRunner(): CustomCommandRunner
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $runner = new CustomCommandRunner($this->httpClient);
        $runner->setWebdriverUri($this->webdriverUri);
        $runner->setUploadDir($this->uploadDir);

        return $runner;
    }

    public function testUpload(): void
    {
        $command = new Command();
        $command->setCommand(CustomCommandRunner::UPLOAD);
        $command->setTarget('id=file_input');
        $command->setValue('sub-directory/file.txt');
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
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'file_input' === $selector->getValue();
        }))->willReturn($this->element);
        $this->runner->run($command, $this->values, $this->driver);
    }

    /**
     * @dataProvider statusCodeProvider
     */
    public function testAssertFileDownloaded(int $code): void
    {
        $command = new Command();
        $command->setCommand(CustomCommandRunner::ASSERT_FILE_DOWNLOADED);
        $command->setTarget('file.txt');
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn($code);
        $this->driver->expects($this->once())->method('getSessionID')->willReturn($this->sessionId);
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                $this->webdriverUri . '/download/' . $this->sessionId . '/file.txt'
            )
            ->willReturn($response);
        if (200 !== $code) {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('File file.txt is not downloaded');
        }
        $this->runner->run($command, $this->values, $this->driver);
    }

    public function statusCodeProvider(): array
    {
        return [
            [200],
            [404],
        ];
    }

    public function testAssertFileDownloadedThrowException(): void
    {
        $command = new Command();
        $command->setCommand(CustomCommandRunner::ASSERT_FILE_DOWNLOADED);
        $command->setTarget('file.txt');
        $this->runner->setWebdriverUri($this->webdriverUri);
        $this->driver->expects($this->once())->method('getSessionID')->willReturn($this->sessionId);
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                $this->webdriverUri . '/download/' . $this->sessionId . '/file.txt'
            )
            ->willThrowException(new HttpClientException('Something wrong'));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Can not verify file file.txt is downloaded: Something wrong');
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

    public function commandsRequireTarget(): array
    {
        return [
            CustomCommandRunner::UPLOAD,
            CustomCommandRunner::ASSERT_FILE_DOWNLOADED,
        ];
    }

    public function commandsRequireValue(): array
    {
        return [
            CustomCommandRunner::UPLOAD,
        ];
    }
}
