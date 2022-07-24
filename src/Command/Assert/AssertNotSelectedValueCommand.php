<?php

namespace Tienvx\Bundle\MbtBundle\Command\Assert;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class AssertNotSelectedValueCommand extends AbstractAssertCommand
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
        return 'Unexpected selected value';
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $select = $this->getSelect($driver->findElement($this->getSelector($target)));
        $elementValue = $select->getFirstSelectedOption()->getAttribute('value');
        $this->assert(
            $elementValue !== $value,
            sprintf('Actual value "%s" did match "%s"', $elementValue, $value)
        );
    }
}
