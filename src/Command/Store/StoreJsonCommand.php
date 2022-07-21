<?php

namespace Tienvx\Bundle\MbtBundle\Command\Store;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class StoreJsonCommand extends AbstractStoreCommand
{
    public static function isTargetRequired(): bool
    {
        return true;
    }

    public static function isValueRequired(): bool
    {
        return true;
    }

    public static function validateTarget(?string $target): bool
    {
        return $target && static::isValidJson($target);
    }

    protected static function isValidJson(string $target): bool
    {
        json_decode($target);

        return JSON_ERROR_NONE === json_last_error();
    }

    public static function getTargetHelper(): string
    {
        return 'Encoded json';
    }

    public static function getValueHelper(): string
    {
        return 'Variable to store json';
    }

    public function validateValue(?string $value): bool
    {
        return !empty($value);
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $values->setValue($value, json_decode($target));
    }
}
