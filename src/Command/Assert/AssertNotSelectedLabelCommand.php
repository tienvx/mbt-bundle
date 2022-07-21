<?php

namespace Tienvx\Bundle\MbtBundle\Command\Assert;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class AssertNotSelectedLabelCommand extends AbstractAssertCommand
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
        return 'Unexpected selected label';
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $select = $this->getSelect($driver->findElement($this->getSelector($target)));
        $elementLabel = $select->getFirstSelectedOption()->getText();
        $this->assert(
            $elementLabel !== $value,
            sprintf('Actual label "%s" did match "%s"', $elementLabel, $value)
        );
    }
}
