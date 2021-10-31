<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

class SelenoidHelper implements SelenoidHelperInterface
{
    protected string $webdriverUri;

    public function setWebdriverUri(string $webdriverUri): void
    {
        $this->webdriverUri = $webdriverUri;
    }

    public function getVideoUrl(string $session): string
    {
        return sprintf('%s/video/%s.mp4', $this->webdriverUri, $session);
    }

    public function getLogUrl(string $session): string
    {
        return sprintf('%s/logs/%s.log', $this->webdriverUri, $session);
    }

    public function createDriver(DesiredCapabilities $capabilities): RemoteWebDriver
    {
        return RemoteWebDriver::create(
            $this->webdriverUri . '/wd/hub',
            $capabilities
        );
    }

    public function getCapabilities(TaskInterface $task, ?int $bugId = null): DesiredCapabilities
    {
        $caps = [
            WebDriverCapabilityType::BROWSER_NAME => $task->getBrowser()->getName(),
            WebDriverCapabilityType::VERSION => $task->getBrowser()->getVersion(),
            'enableVNC' => false,
            'enableLog' => $task->isDebug() || $bugId,
            'enableVideo' => $task->isDebug() || $bugId,
            'name' => sprintf('Executing task %d', $task->getId()),
        ];

        return new DesiredCapabilities($caps);
    }
}
