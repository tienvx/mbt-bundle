<?php

namespace Tienvx\Bundle\MbtBundle\Command\Window;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class SelectWindowCommand extends AbstractWindowCommand
{
    public static function isTargetRequired(): bool
    {
        return true;
    }

    public static function getTargetHelper(): string
    {
        return 'Window handle e.g. A1B2-C3D4';
    }

    public static function validateTarget(?string $target): bool
    {
        return $target && static::isValidHandle($target);
    }

    protected static function isValidHandle(string $target): bool
    {
        return str_starts_with($target, 'handle=') && substr($target, 7);
    }

    protected function getHandle(string $target): string
    {
        return str_starts_with($target, 'handle=') ? substr($target, 7) : $target;
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $driver->switchTo()->window($this->getHandle($target));
    }
}
