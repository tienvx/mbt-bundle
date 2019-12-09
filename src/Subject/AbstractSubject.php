<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;

abstract class AbstractSubject implements SubjectInterface, SubjectScreenshotInterface, SubjectMarkingInterface
{
    /**
     * @var mixed Required by workflow component
     */
    protected $marking;

    /**
     * @var array Required by workflow component
     */
    protected $context;

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    public function setUp(bool $testing = false): void
    {
        // Init system-under-test connection e.g.
        // $this->client = Client::createChromeClient();
    }

    public function tearDown(): void
    {
        // Destroy system-under-test connection e.g.
        // $this->client->quit();
    }

    /**
     * Required by workflow component.
     */
    public function getMarking()
    {
        return $this->marking;
    }

    /**
     * Required by workflow component.
     */
    public function setMarking($marking, array $context = []): void
    {
        $this->marking = $marking;
        $this->context = $context;
    }

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
     *
     * @see https://stackoverflow.com/a/13468943
     */
    public function removeScreenshots($bugId): void
    {
        $this->filesystem->deleteDir("{$bugId}/");
    }
}
