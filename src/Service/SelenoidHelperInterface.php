<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\DebugInterface;

interface SelenoidHelperInterface
{
    public function setWebdriverUri(string $webdriverUri): void;

    public function getVideoUrl(DebugInterface $entity): string;

    public function getLogUrl(DebugInterface $entity): string;

    public function createDriver(DebugInterface $entity): RemoteWebDriver;
}
