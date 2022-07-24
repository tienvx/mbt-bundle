<?php

namespace Tienvx\Bundle\MbtBundle\Command\Assert;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class AssertCommand extends AbstractAssertCommand
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
        return 'Variable to get value from';
    }

    public static function getValueHelper(): string
    {
        return 'Expected value';
    }

    public static function validateTarget(?string $target): bool
    {
        return !empty($target);
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $actual = $values->getValue($target);
        $this->assert(
            $actual === $value,
            sprintf('Actual value "%s" did not match "%s"', $actual, $value)
        );
    }
}
