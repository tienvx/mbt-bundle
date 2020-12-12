<?php

namespace Tienvx\Bundle\MbtBundle\Provider;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;

interface ProviderInterface extends PluginInterface
{
    public const RESOLUTIONS = 'resolutions';
    public const BROWSERS = 'browsers';

    public function getSeleniumServerUrl(string $seleniumServer): string;

    public function getCapabilities(TaskInterface $task, ?int $recordVideoBugId = null): DesiredCapabilities;

    public function getVideoUrl(string $seleniumServer, int $bugId): string;

    public function getPlatforms(): array;

    public function getBrowsers(string $platform): array;

    public function getBrowserVersions(string $platform, string $browser): array;

    public function getResolutions(string $platform): array;

    public function getConfig(): array;
}
