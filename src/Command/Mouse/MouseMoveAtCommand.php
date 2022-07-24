<?php

namespace Tienvx\Bundle\MbtBundle\Command\Mouse;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class MouseMoveAtCommand extends AbstractMousePointCommand
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
        $driver->getMouse()->mouseMove(
            $driver->findElement($this->getSelector($target))->getCoordinates(),
            $point->getX(),
            $point->getY()
        );
    }
}
