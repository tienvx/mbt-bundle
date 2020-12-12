<?php

namespace Tienvx\Bundle\MbtBundle\Model;

class SeleniumConfig implements SeleniumConfigInterface
{
    protected string $provider;
    protected string $platform;
    protected string $browser;
    protected string $browserVersion;
    protected string $resolution;

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
    }

    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function setPlatform(string $platform): void
    {
        $this->platform = $platform;
    }

    public function getBrowser(): string
    {
        return $this->browser;
    }

    public function setBrowser(string $browser): void
    {
        $this->browser = $browser;
    }

    public function getBrowserVersion(): string
    {
        return $this->browserVersion;
    }

    public function setBrowserVersion(string $browserVersion): void
    {
        $this->browserVersion = $browserVersion;
    }

    public function getResolution(): string
    {
        return $this->resolution;
    }

    public function setResolution(string $resolution): void
    {
        $this->resolution = $resolution;
    }
}
