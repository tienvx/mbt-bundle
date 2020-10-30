<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Selenium\Helper;

class Selenium implements SeleniumInterface
{
    protected ConfigLoaderInterface $configLoader;
    protected string $dsn;

    public function __construct(ConfigLoaderInterface $configLoader)
    {
        $this->configLoader = $configLoader;
    }

    public function setDsn(string $dsn): void
    {
        $this->dsn = $dsn;
    }

    public function createHelper(): Helper
    {
        return new Helper($this->createDriver($this->dsn, $this->configLoader->getCapabilities()));
    }

    protected function createDriver(string $url, array $capabilities): RemoteWebDriver
    {
        return RemoteWebDriver::create($url, $capabilities);
    }
}
