<?php

namespace Tienvx\Bundle\MbtBundle\Provider;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

abstract class AbstractProvider implements ProviderInterface
{
    protected ?array $config = null;

    public static function getManager(): string
    {
        return ProviderManager::class;
    }

    public static function isSupported(): bool
    {
        return true;
    }

    /**
     * @throws ExceptionInterface
     */
    public function getSeleniumServerUrl(string $seleniumServer): string
    {
        return $seleniumServer . '/wd/hub';
    }

    public function getCapabilities(TaskInterface $task, ?int $recordVideoBugId = null): DesiredCapabilities
    {
        return new DesiredCapabilities();
    }

    public function getPlatforms(): array
    {
        return array_keys($this->getConfig());
    }

    public function getBrowsers(string $platform): array
    {
        return array_keys($this->getConfig()[$platform][ProviderInterface::BROWSERS] ?? []);
    }

    public function getBrowserVersions(string $platform, string $browser): array
    {
        return $this->getConfig()[$platform][ProviderInterface::BROWSERS][$browser] ?? [];
    }

    public function getResolutions(string $platform): array
    {
        return $this->getConfig()[$platform][ProviderInterface::RESOLUTIONS] ?? [];
    }

    public function getConfig(): array
    {
        if (!isset($this->config)) {
            $this->config = $this->loadConfig();
        }

        return $this->config;
    }

    protected function loadConfig(): array
    {
        return [];
    }
}
