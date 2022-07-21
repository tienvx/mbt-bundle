<?php

namespace Tienvx\Bundle\MbtBundle\Command\Window;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class CloseCommand extends AbstractWindowCommand
{
    public static function isTargetRequired(): bool
    {
        return false;
    }

    public static function getTargetHelper(): string
    {
        return '';
    }

    public static function validateTarget(?string $target): bool
    {
        return true;
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $driver->close();
    }
}
