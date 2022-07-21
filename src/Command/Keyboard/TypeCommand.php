<?php

namespace Tienvx\Bundle\MbtBundle\Command\Keyboard;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class TypeCommand extends AbstractKeyboardCommand
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
        return 'Text to be filled into target';
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $driver
            ->findElement($this->getSelector($target))
            ->click()
            ->clear()
            ->sendKeys($value);
    }
}
