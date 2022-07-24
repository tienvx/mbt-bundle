<?php

namespace Tienvx\Bundle\MbtBundle\Command\Mouse;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class DoubleClickAtCommand extends AbstractMousePointCommand
{
    public static function isTargetRequired(): bool
    {
        return true;
    }

    public static function isValueRequired(): bool
    {
        return true;
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $point = $this->getPoint($value);
        $driver->action()->moveToElement(
            $driver->findElement($this->getSelector($target)),
            $point->getX(),
            $point->getY()
        )->doubleClick()->perform();
    }
}
