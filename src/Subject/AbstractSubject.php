<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

abstract class AbstractSubject implements SubjectInterface
{
    /**
     * @var string Required by workflow component
     */
    public $marking;

    /**
     * @var bool
     */
    protected $testing = false;

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
     * @var string
     */
    protected $screenshotsDir = '';

    /**
     * @param $testing boolean
     */
    public function setTesting(bool $testing = false)
    {
        $this->testing = $testing;
    }

    /**
     * @return bool
     */
    public function isTesting()
    {
        return $this->testing;
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
     * @param string $screenshotsDir
     */
    public function setScreenshotsDir(string $screenshotsDir)
    {
        $this->screenshotsDir = rtrim($screenshotsDir, '/');
        if (!is_dir($this->screenshotsDir)) {
            mkdir($this->screenshotsDir, 0777, true);
        }
    }

    public function captureScreenshot($bugId, $index)
    {
        if (!is_dir($this->screenshotsDir."/{$bugId}")) {
            mkdir($this->screenshotsDir."/{$bugId}", 0777, true);
        }
        file_put_contents($this->screenshotsDir."/{$bugId}/{$index}.png", '');
    }

    public function getScreenshot($bugId, $index)
    {
        if (file_exists($this->screenshotsDir."/{$bugId}/{$index}.png")) {
            return file_get_contents($this->screenshotsDir."/{$bugId}/{$index}.png");
        } else {
            return '';
        }
    }

    public function isImageScreenshot()
    {
        return true;
    }

    public function hasScreenshot($bugId, $index)
    {
        return file_exists($this->screenshotsDir."/{$bugId}/{$index}.png");
    }

    /**
     * @param $bugId
     *
     * @see https://stackoverflow.com/a/13468943
     */
    public function removeScreenshots($bugId)
    {
        if (is_dir($this->screenshotsDir."/{$bugId}")) {
            @array_map('unlink', glob($this->screenshotsDir."/{$bugId}/*"));
            rmdir($this->screenshotsDir."/{$bugId}");
        }
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
