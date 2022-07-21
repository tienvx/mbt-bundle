<?php

namespace Tienvx\Bundle\MbtBundle\Command\Store;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class StoreValueCommand extends AbstractStoreCommand
{
    public static function isTargetRequired(): bool
    {
        return true;
    }

    public static function isValueRequired(): bool
    {
        return true;
    }

    public static function getValueHelper(): string
    {
        return 'Variable to store element value';
    }

    public function validateValue(?string $value): bool
    {
        return !empty($value);
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $values->setValue(
            $value,
            $driver->findElement($this->getSelector($target))->getAttribute('value')
        );
    }
}
