<?php

namespace Tienvx\Bundle\MbtBundle\Command\Assert;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class AssertTitleCommand extends AbstractAssertCommand
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
        return 'Expected title';
    }

    public static function validateTarget(?string $target): bool
    {
        return !is_null($target);
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $this->assert(
            $driver->getTitle() === $target,
            sprintf('Actual title "%s" did not match "%s"', $driver->getTitle(), $target)
        );
    }
}
