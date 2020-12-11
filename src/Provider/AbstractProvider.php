<?php

namespace Tienvx\Bundle\MbtBundle\Provider;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

abstract class AbstractProvider implements ProviderInterface
{
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

    public static function getOperatingSystems(): array
    {
        return array_keys(static::loadConfig());
    }

    public static function getBrowsers(string $platform): array
    {
        return static::loadConfig()[$platform][ProviderInterface::BROWSERS] ?? [];
    }

    public static function getResolutions(string $platform): array
    {
        return static::loadConfig()[$platform][ProviderInterface::RESOLUTIONS] ?? [];
    }

    protected static function loadConfig(): array
    {
        return [];
    }
}
