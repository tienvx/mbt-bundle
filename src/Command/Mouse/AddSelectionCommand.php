<?php

namespace Tienvx\Bundle\MbtBundle\Command\Mouse;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class AddSelectionCommand extends AbstractMouseSelectionCommand
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
        return 'Select by e.g. index=12, value=something or label=something else';
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $select = $this->getSelect($driver->findElement($this->getSelector($target)));
        if (str_starts_with($value, 'index=')) {
            $select->selectByIndex((int) substr($value, 6));
        } elseif (str_starts_with($value, 'value=')) {
            $select->selectByValue(substr($value, 6));
        } elseif (str_starts_with($value, 'label=')) {
            $select->selectByVisibleText(substr($value, 6));
        }
    }
}
