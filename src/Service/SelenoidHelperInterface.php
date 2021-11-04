<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

interface SelenoidHelperInterface
{
    public function setWebdriverUri(string $webdriverUri): void;

    public function getVideoUrl(TaskInterface|BugInterface $entity): string;

    public function getLogUrl(TaskInterface|BugInterface $entity): string;

    public function createDriver(DesiredCapabilities $capabilities): RemoteWebDriver;

    public function getCapabilities(TaskInterface|BugInterface $entity, bool $debug = false): DesiredCapabilities;

    public function getVideoName(TaskInterface|BugInterface $entity): string;

    public function getLogName(TaskInterface|BugInterface $entity): string;
}
