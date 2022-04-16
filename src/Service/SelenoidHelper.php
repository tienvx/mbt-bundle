<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Tienvx\Bundle\MbtBundle\Model\DebugInterface;

class SelenoidHelper implements SelenoidHelperInterface
{
    protected const WAIT_SECONDS = 2;

    protected string $webdriverUri;

    public function setWebdriverUri(string $webdriverUri): void
    {
        $this->webdriverUri = $webdriverUri;
    }

    public function getVideoUrl(DebugInterface $entity): string
    {
        return sprintf('%s/video/%s', $this->webdriverUri, $entity->getVideoName());
    }

    public function getLogUrl(DebugInterface $entity): string
    {
        return sprintf('%s/logs/%s', $this->webdriverUri, $entity->getLogName());
    }

    public function createDriver(DebugInterface $entity): RemoteWebDriver
    {
        $driver = $this->createDriverInternal(
            $this->webdriverUri . '/wd/hub',
            $this->getCapabilities($entity)
        );
        if ($entity->isDebug()) {
            $this->waitForVideoContainer();
        }

        return $driver;
    }

    protected function getCapabilities(DebugInterface $entity): DesiredCapabilities
    {
        $caps = [
            WebDriverCapabilityType::BROWSER_NAME => $entity->getTask()->getBrowser()->getName(),
            WebDriverCapabilityType::VERSION => $entity->getTask()->getBrowser()->getVersion(),
            'enableVNC' => false,
            'enableLog' => $entity->isDebug(),
            'enableVideo' => $entity->isDebug(),
        ];
        if ($entity->isDebug()) {
            $caps += [
                'logName' => $entity->getLogName(),
                'videoName' => $entity->getVideoName(),
                'videoFrameRate' => 60,
            ];
        }

        return new DesiredCapabilities($caps);
    }

    protected function waitForVideoContainer(): void
    {
        sleep(static::WAIT_SECONDS);
    }

    protected function createDriverInternal($serverUrl, DesiredCapabilities $capabilities): RemoteWebDriver
    {
        return RemoteWebDriver::create($serverUrl, $capabilities);
    }
}
