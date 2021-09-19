<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

interface SelenoidHelperInterface
{
    public function setWebdriverUri(string $webdriverUri): void;

    public function getVideoUrl(int $bugId): string;

    public function getVideoFilename(int $bugId): string;

    public function createDriver(DesiredCapabilities $capabilities): RemoteWebDriver;

    public function getCapabilities(TaskInterface $task, ?int $bugId = null): DesiredCapabilities;
}
