<?php

namespace Tienvx\Bundle\MbtBundle\Command\Mouse;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class RemoveSelectionCommand extends AbstractMouseSelectionCommand
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
        return 'Deselect by e.g. index=12, value=something or label=something else';
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $select = $this->getSelect($driver->findElement($this->getSelector($target)));
        if (str_starts_with($value, 'index=')) {
            $select->deselectByIndex((int) substr($value, 6));
        } elseif (str_starts_with($value, 'value=')) {
            $select->deselectByValue(substr($value, 6));
        } elseif (str_starts_with($value, 'label=')) {
            $select->deselectByVisibleText(substr($value, 6));
        }
    }
}
