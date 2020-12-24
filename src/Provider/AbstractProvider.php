<?php

namespace Tienvx\Bundle\MbtBundle\Provider;

use Facebook\WebDriver\Remote\DesiredCapabilities;
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
}
