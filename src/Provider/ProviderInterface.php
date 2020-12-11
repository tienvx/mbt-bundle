<?php

namespace Tienvx\Bundle\MbtBundle\Provider;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;

interface ProviderInterface extends PluginInterface
{
    public const RESOLUTIONS = 'resolutions';
    public const BROWSERS = 'browsers';

    public function setSeleniumServer(string $seleniumServer): void;

    public function createDriver(TaskInterface $task, ?int $recordVideoBugId = null): RemoteWebDriver;

    public function getSeleniumServerUrl(): string;

    public function getCapabilities(TaskInterface $task, ?int $recordVideoBugId = null): DesiredCapabilities;

    public function getVideoUrl(int $bugId): string;

    public static function getOperatingSystems(): array;

    public static function getBrowsers(string $platform): array;

    public static function getResolutions(string $platform): array;
}
