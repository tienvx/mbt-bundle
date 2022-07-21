<?php

namespace Tienvx\Bundle\MbtBundle\Command\Assert;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class AssertElementPresentCommand extends AbstractAssertCommand
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
        $this->assert(
            count($driver->findElements($this->getSelector($target))) > 0,
            sprintf('Expected element "%s" was not found in page', $target)
        );
    }
}
