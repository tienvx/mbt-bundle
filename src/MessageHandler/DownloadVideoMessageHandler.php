<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Message\DownloadVideoMessage;

class DownloadVideoMessageHandler implements MessageHandlerInterface
{
    protected FilesystemOperator $defaultStorage;

    public function __construct(FilesystemOperator $defaultStorage)
    {
        $this->defaultStorage = $defaultStorage;
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(DownloadVideoMessage $message): void
    {
        $stream = fopen($message->getVideoUrl(), 'r');
        try {
            $this->defaultStorage->writeStream(sprintf('/bug/%d.mp4', $message->getBugId()), $stream);
        } catch (FilesystemException $exception) {
            // Video file is not available at this time, try later.
            throw new RuntimeException(sprintf('Can not download video for bug %d', $message->getBugId()));
        }
    }
}
