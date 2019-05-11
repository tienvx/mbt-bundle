<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;

abstract class AbstractSubject implements SubjectInterface
{
    /**
     * @var string Required by workflow component
     */
    public $marking;

    /**
     * @var bool
     */
    protected $testingModel = false;

    /**
     * @var bool
     */
    protected $testingSubject = false;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $storedData = [];

    /**
     * @var bool
     */
    protected $needData = true;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param $testingModel bool
     */
    public function setTestingModel(bool $testingModel = false)
    {
        $this->testingModel = $testingModel;
    }

    /**
     * @return bool
     */
    public function isTestingModel()
    {
        return $this->testingModel;
    }

    /**
     * @param $testingSubject bool
     */
    public function setTestingSubject(bool $testingSubject = false)
    {
        $this->testingSubject = $testingSubject;
    }

    /**
     * @return bool
     */
    public function isTestingSubject()
    {
        return $this->testingSubject;
    }

    /**
     * @param $needData boolean
     */
    public function setNeedData(bool $needData = true)
    {
        $this->needData = $needData;
    }

    /**
     * @return bool
     */
    public function needData()
    {
        return $this->needData;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function storeData()
    {
        $this->storedData = $this->data;
    }

    /**
     * @return array
     */
    public function getStoredData(): array
    {
        return $this->storedData;
    }

    public function applyTransition(string $transitionName)
    {
        if (method_exists($this, $transitionName)) {
            call_user_func([$this, $transitionName]);
        }
    }

    public function enterPlace(array $places)
    {
        foreach ($places as $place) {
            if (method_exists($this, $place)) {
                call_user_func([$this, $place]);
            }
        }
    }

    /**
     * @param Filesystem $filesystem
     */
    public function setFilesystem(Filesystem $filesystem)
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
