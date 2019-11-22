<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use League\Flysystem\FilesystemInterface;

interface SubjectInterface
{
    public function setUp(bool $testing = false): void;

    public function tearDown(): void;

    public function getScreenshotUrl($bugId, $index): string;

    public function setFilesystem(FilesystemInterface $filesystem): void;

    public function removeScreenshots($bugId): void;

    public function captureScreenshot($bugId, $index): void;

    public function getScreenshot($bugId, $index): string;

    public function isImageScreenshot(): bool;

    public function hasScreenshot($bugId, $index): bool;
}
