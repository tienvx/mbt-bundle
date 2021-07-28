<?php


namespace Tienvx\Bundle\MbtBundle\Service;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

class SelenoidHelper implements SelenoidHelperInterface
{
    protected string $selenoidServer;

    public function setSelenoidServer(string $selenoidServer): void
    {
        $this->selenoidServer = $selenoidServer;
    }

    public function getVideoUrl(int $bugId): string
    {
        return sprintf('%s/video/%s', $this->selenoidServer, $this->getVideoFilename($bugId));
    }

    public function getVideoFilename(int $bugId): string
    {
        return sprintf('bug-%s.mp4', $bugId);
    }

    public function createDriver(DesiredCapabilities $capabilities): RemoteWebDriver
    {
        return RemoteWebDriver::create(
            $this->selenoidServer,
            $capabilities
        );
    }

    public function getCapabilities(TaskInterface $task, ?int $bugId = null): DesiredCapabilities
    {
        $caps = [];
        if ($bugId) {
            $caps = [
                'enableVideo' => true,
                'videoName' => sprintf('bug-%d.mp4', $bugId),
                'name' => sprintf('Recording video for bug %d', $bugId),
            ];
        }
        $caps += [
            WebDriverCapabilityType::BROWSER_NAME => $task->getBrowser(),
            WebDriverCapabilityType::VERSION => $task->getBrowserVersion(),
            'enableVNC' => true,
            'enableLog' => true,
            'name' => sprintf('Executing task %d', $task->getId()),
        ];

        return new DesiredCapabilities($caps);
    }
}
