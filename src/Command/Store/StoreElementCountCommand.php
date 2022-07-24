<?php

namespace Tienvx\Bundle\MbtBundle\Command\Store;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class StoreElementCountCommand extends AbstractStoreCommand
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
        return 'Variable to store element count';
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
            count($driver->findElements($this->getSelector($target)))
        );
    }
}
