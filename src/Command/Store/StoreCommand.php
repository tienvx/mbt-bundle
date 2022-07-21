<?php

namespace Tienvx\Bundle\MbtBundle\Command\Store;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class StoreCommand extends AbstractStoreCommand
{
    public static function isTargetRequired(): bool
    {
        return false;
    }

    public static function isValueRequired(): bool
    {
        return true;
    }

    public static function validateTarget(?string $target): bool
    {
        return true;
    }

    public function validateValue(?string $value): bool
    {
        return !empty($value);
    }

    public static function getTargetHelper(): string
    {
        return 'Value to store';
    }

    public static function getValueHelper(): string
    {
        return 'Variable to store value';
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $values->setValue($value, $target);
    }
}
