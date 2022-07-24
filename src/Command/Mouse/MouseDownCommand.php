<?php

namespace Tienvx\Bundle\MbtBundle\Command\Mouse;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class MouseDownCommand extends AbstractMouseCommand
{
    public static function isTargetRequired(): bool
    {
        return true;
    }

    public static function isValueRequired(): bool
    {
        return false;
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $driver->getMouse()->mouseDown(
            $driver->findElement($this->getSelector($target))->getCoordinates()
        );
    }
}
