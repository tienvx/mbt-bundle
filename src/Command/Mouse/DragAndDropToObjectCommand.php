<?php

namespace Tienvx\Bundle\MbtBundle\Command\Mouse;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class DragAndDropToObjectCommand extends AbstractMouseCommand
{
    public static function isTargetRequired(): bool
    {
        return true;
    }

    public static function isValueRequired(): bool
    {
        return true;
    }

    public static function getTargetHelper(): string
    {
        return "Source locator e.g. 'id=email' or 'css=.last-name'";
    }

    public static function getValueHelper(): string
    {
        return "Target locator e.g. 'id=email' or 'css=.last-name'";
    }

    public function validateValue(?string $value): bool
    {
        return static::validateTarget($value);
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $driver->action()->dragAndDrop(
            $driver->findElement($this->getSelector($target)),
            $driver->findElement($this->getSelector($value))
        )->perform();
    }
}
