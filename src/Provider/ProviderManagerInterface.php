<?php

namespace Tienvx\Bundle\MbtBundle\Provider;

interface ProviderManagerInterface
{
    public function getSeleniumServer(string $provider): string;

    public function getProviders(): array;

    public function getPlatforms(string $provider): array;

    public function getBrowsers(string $provider, string $platform): array;

    public function getBrowserVersions(string $provider, string $platform, string $browser): array;

    public function getResolutions(string $provider, string $platform): array;
}
