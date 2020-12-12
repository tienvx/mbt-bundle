<?php

namespace Tienvx\Bundle\MbtBundle\Model;

interface SeleniumConfigInterface
{
    public function getProvider(): string;

    public function setProvider(string $provider): void;

    public function getPlatform(): string;

    public function setPlatform(string $platform): void;

    public function getBrowser(): string;

    public function setBrowser(string $browser): void;

    public function getBrowserVersion(): string;

    public function setBrowserVersion(string $browserVersion): void;

    public function getResolution(): string;

    public function setResolution(string $resolution): void;
}
