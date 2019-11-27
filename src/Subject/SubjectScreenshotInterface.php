<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use League\Flysystem\FilesystemInterface;

interface SubjectScreenshotInterface
{
    public function setFilesystem(FilesystemInterface $filesystem): void;

    public function removeScreenshots($bugId): void;

    public function captureScreenshot($bugId, $index): void;

    public function getScreenshot($bugId, $index): string;

    public function isImageScreenshot(): bool;

    public function hasScreenshot($bugId, $index): bool;
}
