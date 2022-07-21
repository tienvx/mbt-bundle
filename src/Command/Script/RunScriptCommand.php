<?php

namespace Tienvx\Bundle\MbtBundle\Command\Script;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class RunScriptCommand extends AbstractScriptCommand
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
        return 'Script to run';
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $driver->executeScript($target);
    }
}
