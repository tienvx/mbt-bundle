<?php

namespace Tienvx\Bundle\MbtBundle\Command\Window;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class OpenCommand extends AbstractWindowCommand
{
    public static function isTargetRequired(): bool
    {
        return true;
    }

    public static function getTargetHelper(): string
    {
        return 'Url';
    }

    public static function validateTarget(?string $target): bool
    {
        return $target && static::isValidUrl($target);
    }

    /**
     * TODO Find a solution better than this.
     */
    protected static function isValidUrl(string $target): bool
    {
        $url = filter_var($target, FILTER_SANITIZE_URL);

        return filter_var($url, FILTER_VALIDATE_URL);
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $driver->get($target);
    }
}
