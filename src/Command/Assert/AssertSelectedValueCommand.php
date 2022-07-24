<?php

namespace Tienvx\Bundle\MbtBundle\Command\Assert;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class AssertSelectedValueCommand extends AbstractAssertCommand
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
        return 'Expected selected value';
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $select = $this->getSelect($driver->findElement($this->getSelector($target)));
        $elementValue = $select->getFirstSelectedOption()->getAttribute('value');
        $this->assert(
            $elementValue === $value,
            sprintf('Actual value "%s" did not match "%s"', $elementValue, $value)
        );
    }
}
