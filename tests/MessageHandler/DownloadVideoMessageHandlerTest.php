<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use League\Flysystem\FilesystemWriter;
use League\Flysystem\UnableToWriteFile;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Message\DownloadVideoMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\DownloadVideoMessageHandler;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\DownloadVideoMessageHandler
 * @covers \Tienvx\Bundle\MbtBundle\Message\DownloadVideoMessage
 */
class DownloadVideoMessageHandlerTest extends TestCase
{
    protected FilesystemWriter $filesystemWriter;
    protected DownloadVideoMessageHandler $handler;

    protected function setUp(): void
    {
        $this->filesystemWriter = $this->createMock(FilesystemWriter::class);
        $this->handler = new DownloadVideoMessageHandler($this->filesystemWriter);
    }

    public function testInvokeFailedToWrite(): void
    {
        $exception = UnableToWriteFile::atLocation(__DIR__ . '/../Fixtures/video.mp4', 'not valid video file');
        $this->expectException(UnableToWriteFile::class);
        $this->filesystemWriter
            ->expects($this->once())
            ->method('writeStream')
            ->with('/bug/123.mp4', $this->callback(fn ($stream) => is_resource($stream)))
            ->willThrowException($exception);
        $message = new DownloadVideoMessage(123, __DIR__ . '/../Fixtures/video.mp4');
        call_user_func($this->handler, $message);
    }

    public function testInvokeDownloadedVideoAndWriteSuccess(): void
    {
        $this->filesystemWriter
            ->expects($this->once())
            ->method('writeStream')
            ->with('/bug/123.mp4', $this->callback(fn ($stream) => is_resource($stream)));
        $message = new DownloadVideoMessage(123, __DIR__ . '/../Fixtures/video.mp4');
        call_user_func($this->handler, $message);
    }
}
