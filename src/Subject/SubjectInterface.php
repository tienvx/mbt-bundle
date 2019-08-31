<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use League\Flysystem\FilesystemInterface;

interface SubjectInterface
{
    public function setUp();

    public function tearDown();

    public function getScreenshotUrl($bugId, $index);

    public function setFilesystem(FilesystemInterface $filesystem);

    public function removeScreenshots($bugId);

    public function captureScreenshot($bugId, $index);

    public function getScreenshot($bugId, $index);

    public function isImageScreenshot();

    public function hasScreenshot($bugId, $index);
}
