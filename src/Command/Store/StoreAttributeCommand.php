<?php

namespace Tienvx\Bundle\MbtBundle\Command\Store;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class StoreAttributeCommand extends AbstractStoreCommand
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
        return $target && static::isValidAttribute($target);
    }

    protected static function isValidAttribute(string $target): bool
    {
        list($elementLocator, $attribute) = array_pad(explode('@', $target, 2), 2, null);

        return static::isValidSelector($elementLocator) && !empty($attribute);
    }

    public static function getTargetHelper(): string
    {
        return "Attribute locator e.g. 'id=email@readonly' or 'css=.last-name@size'";
    }

    public static function getValueHelper(): string
    {
        return 'Variable to store attribute value';
    }

    public function validateValue(?string $value): bool
    {
        return !empty($value);
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        list($elementLocator, $attributeName) = explode('@', $target, 2);
        $values->setValue(
            $value,
            $driver->findElement($this->getSelector($elementLocator))->getAttribute($attributeName)
        );
    }
}
