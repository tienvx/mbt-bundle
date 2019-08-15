<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;

abstract class AbstractSubject implements SubjectInterface
{
    /**
     * @var mixed Required by workflow component
     */
    private $marking;

    /**
     * @var mixed Required by workflow component
     */
    private $context;

    /**
     * @var bool
     */
    protected $testingModel = false;

    /**
     * @var bool
     */
    protected $testingSubject = false;

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    public function __construct($marking = null)
    {
        $this->marking = $marking;
        $this->context = [];
    }

    public function getMarking()
    {
        return $this->marking;
    }

    public function setMarking($marking, array $context = [])
    {
        $this->marking = $marking;
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public static function support(): bool
    {
        return true;
    }

    /**
     * @param bool $testingModel
     */
    public function setTestingModel(bool $testingModel = false)
    {
        $this->testingModel = $testingModel;
    }

    /**
     * @param bool $testingSubject
     */
    public function setTestingSubject(bool $testingSubject = false)
    {
        $this->testingSubject = $testingSubject;
    }

    /**
     * @param FilesystemInterface $filesystem
     */
    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function captureScreenshot($bugId, $index)
    {
        $this->filesystem->put("{$bugId}/{$index}.png", '');
    }

    public function getScreenshot($bugId, $index)
    {
        try {
            return $this->filesystem->read("{$bugId}/{$index}.png");
        } catch (FileNotFoundException $e) {
            return '';
        }
    }

    public function isImageScreenshot()
    {
        return true;
    }

    public function hasScreenshot($bugId, $index)
    {
        return $this->filesystem->has("{$bugId}/{$index}.png");
    }

    /**
     * @param $bugId
     *
     * @see https://stackoverflow.com/a/13468943
     */
    public function removeScreenshots($bugId)
    {
        $this->filesystem->deleteDir("$bugId/");
    }

    public function getScreenshotUrl($bugId, $index)
    {
        return '';
    }

    public function setUp()
    {
        // Init system-under-test connection e.g.
        // $this->client = Client::createChromeClient();
    }

    public function tearDown()
    {
        // Destroy system-under-test connection e.g.
        // $this->client->quit();
    }
}
