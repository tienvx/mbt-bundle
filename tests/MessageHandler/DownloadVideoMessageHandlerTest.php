<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToWriteFile;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Message\DownloadVideoMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\DownloadVideoMessageHandler;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\DownloadVideoMessageHandler
 * @covers \Tienvx\Bundle\MbtBundle\Message\DownloadVideoMessage
 */
class DownloadVideoMessageHandlerTest extends TestCase
{
    protected FilesystemOperator $defaultStorage;
    protected DownloadVideoMessageHandler $handler;

    protected function setUp(): void
    {
        $this->defaultStorage = $this->createMock(FilesystemOperator::class);
        $this->handler = new DownloadVideoMessageHandler($this->defaultStorage);
    }

    public function testInvokeFailedToWrite(): void
    {
        $exception = UnableToWriteFile::atLocation(__DIR__ . '/../Fixtures/video.mp4', 'not valid video file');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Can not download video for bug 123');
        $this->defaultStorage
            ->expects($this->once())
            ->method('writeStream')
            ->with('/bug/123.mp4', $this->callback(fn ($stream) => is_resource($stream)))
            ->willThrowException($exception);
        $message = new DownloadVideoMessage(123, __DIR__ . '/../Fixtures/video.mp4');
        call_user_func($this->handler, $message);
    }

    public function testInvokeDownloadedVideoAndWriteSuccess(): void
    {
        $this->defaultStorage
            ->expects($this->once())
            ->method('writeStream')
            ->with('/bug/123.mp4', $this->callback(fn ($stream) => is_resource($stream)));
        $message = new DownloadVideoMessage(123, __DIR__ . '/../Fixtures/video.mp4');
        call_user_func($this->handler, $message);
    }
}
