<?php

namespace Tienvx\Bundle\MbtBundle\Command\Assert;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class AssertAlertCommand extends AbstractAssertCommand
{
    public static function isTargetRequired(): bool
    {
        return true;
    }

    public static function isValueRequired(): bool
    {
        return false;
    }

    public static function getTargetHelper(): string
    {
        return 'Expected value';
    }

    public static function validateTarget(?string $target): bool
    {
        return !is_null($target);
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $alertText = $driver->switchTo()->alert()->getText();
        $this->assert(
            $alertText === $target,
            sprintf('Actual alert text "%s" did not match "%s"', $alertText, $target)
        );
    }
}
