<?php

namespace Tienvx\Bundle\MbtBundle\Command\Store;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class StoreTitleCommand extends AbstractStoreCommand
{
    public static function isTargetRequired(): bool
    {
        return true;
    }

    public static function isValueRequired(): bool
    {
        return false;
    }

    public static function getTargetHelper(): string
    {
        return 'Variable to store page title';
    }

    public static function validateTarget(?string $target): bool
    {
        return !empty($target);
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $values->setValue($target, $driver->getTitle());
    }
}
