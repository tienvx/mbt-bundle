<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

class SelenoidHelper implements SelenoidHelperInterface
{
    private const BUG = 'bug';
    private const TASK = 'task';

    protected string $webdriverUri;

    public function setWebdriverUri(string $webdriverUri): void
    {
        $this->webdriverUri = $webdriverUri;
    }

    public function getVideoUrl(TaskInterface|BugInterface $entity): string
    {
        return sprintf('%s/video/%s', $this->webdriverUri, $this->getVideoName($entity));
    }

    public function getLogUrl(TaskInterface|BugInterface $entity): string
    {
        return sprintf('%s/logs/%s', $this->webdriverUri, $this->getLogName($entity));
    }

    public function createDriver(DesiredCapabilities $capabilities): RemoteWebDriver
    {
        return RemoteWebDriver::create(
            $this->webdriverUri . '/wd/hub',
            $capabilities
        );
    }

    public function getCapabilities(TaskInterface|BugInterface $entity, bool $debug = false): DesiredCapabilities
    {
        $task = $entity instanceof BugInterface ? $entity->getTask() : $entity;
        $caps = [
            WebDriverCapabilityType::BROWSER_NAME => $task->getBrowser()->getName(),
            WebDriverCapabilityType::VERSION => $task->getBrowser()->getVersion(),
            'enableVNC' => false,
            'enableLog' => $debug,
            'enableVideo' => $debug,
        ];
        if ($debug) {
            $caps += [
                'logName' => $this->getLogName($entity),
                'videoName' => $this->getVideoName($entity),
                'videoFrameRate' => 60,
            ];
        }

        return new DesiredCapabilities($caps);
    }

    public function getVideoName(TaskInterface|BugInterface $entity): string
    {
        return sprintf('%s-%d.mp4', $entity instanceof TaskInterface ? static::TASK : static::BUG, $entity->getId());
    }

    public function getLogName(TaskInterface|BugInterface $entity): string
    {
        return sprintf('%s-%d.log', $entity instanceof TaskInterface ? static::TASK : static::BUG, $entity->getId());
    }
}
