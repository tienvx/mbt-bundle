<?php

namespace Tienvx\Bundle\MbtBundle\Command\Script;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class ExecuteAsyncScriptCommand extends AbstractScriptCommand
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
        return 'Async script to run';
    }

    public static function getValueHelper(): string
    {
        return 'Variable to set value (optional)';
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $result = $driver->executeAsyncScript($target);
        if ($value) {
            $values->setValue($value, $result);
        }
    }
}
