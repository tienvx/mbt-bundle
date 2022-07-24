<?php

namespace Tienvx\Bundle\MbtBundle\Command\Custom;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractHasHttpClientCommand extends AbstractCustomCommand
{
    public function __construct(protected HttpClientInterface $httpClient)
    {
    }

    public static function isTargetRequired(): bool
    {
        return true;
    }

    public static function isValueRequired(): bool
    {
        return false;
    }

    protected function getUrl(string $type, RemoteWebDriver $driver): string
    {
        return sprintf(
            '%s/%s/%s',
            rtrim($driver->getCommandExecutor()->getAddressOfRemoteServer(), '/wd/hub'),
            $type,
            $driver->getSessionID()
        );
    }
}
