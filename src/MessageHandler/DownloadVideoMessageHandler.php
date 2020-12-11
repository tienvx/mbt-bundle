<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemWriter;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Message\DownloadVideoMessage;

class DownloadVideoMessageHandler implements MessageHandlerInterface
{
    protected FilesystemWriter $filesystem;

    public function __construct(FilesystemWriter $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @throws FilesystemException
     */
    public function __invoke(DownloadVideoMessage $message): void
    {
        $stream = fopen($message->getVideoUrl(), 'r');
        try {
            $this->filesystem->writeStream(sprintf('/bug/%d.mp4', $message->getBugId()), $stream);
        } catch (FilesystemException | UnableToWriteFile $exception) {
            // Video file is not available at this time, try later.
            throw $exception;
        }
    }
}
