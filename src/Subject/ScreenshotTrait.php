<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;

trait ScreenshotTrait
{
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    public function setFilesystem(FilesystemInterface $filesystem): void
    {
        $this->filesystem = $filesystem;
    }

    public function captureScreenshot($bugId, $index): void
    {
        $this->filesystem->put("{$bugId}/{$index}.png", '');
    }

    public function getScreenshot($bugId, $index): string
    {
        try {
            return $this->filesystem->read("{$bugId}/{$index}.png");
        } catch (FileNotFoundException $e) {
            return '';
        }
    }

    public function isImageScreenshot(): bool
    {
        return true;
    }

    public function hasScreenshot($bugId, $index): bool
    {
        return $this->filesystem->has("{$bugId}/{$index}.png");
    }

    /**
     * @param $bugId
     */
    public function removeScreenshots($bugId): void
    {
        $this->filesystem->deleteDir("{$bugId}/");
    }
}
