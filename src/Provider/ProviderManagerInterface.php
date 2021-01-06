<?php

namespace Tienvx\Bundle\MbtBundle\Provider;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Plugin\PluginManagerInterface;

interface ProviderManagerInterface extends PluginManagerInterface
{
    public function getProvider(string $name): ProviderInterface;

    public function createDriver(TaskInterface $task, ?int $recordVideoBugId = null): RemoteWebDriver;

    public function getSeleniumServer(string $provider): string;

    public function getProviders(): array;

    public function getPlatforms(string $provider): array;

    public function getBrowsers(string $provider, string $platform): array;

    public function getBrowserVersions(string $provider, string $platform, string $browser): array;

    public function getResolutions(string $provider, string $platform): array;
}
