<?php

namespace Tienvx\Bundle\MbtBundle\Command\Window;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class SetWindowSizeCommand extends AbstractWindowCommand
{
    public static function isTargetRequired(): bool
    {
        return true;
    }

    public static function getTargetHelper(): string
    {
        return 'Dimension e.g. 1280x720';
    }

    public static function validateTarget(?string $target): bool
    {
        return $target && static::isValidDimension($target);
    }

    protected static function isValidDimension(string $target): bool
    {
        return 2 === count([$width, $height] = array_pad(explode('x', $target), 2, null)) && $width && $height;
    }

    protected function getDimension(string $target): WebDriverDimension
    {
        list($width, $height) = explode('x', $target);

        return new WebDriverDimension((int) $width, (int) $height);
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $driver->manage()->window()->setSize($this->getDimension($target));
    }
}
