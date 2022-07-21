<?php

namespace Tienvx\Bundle\MbtBundle\Command\Alert;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class DismissPromptCommand extends AbstractAlertCommand
{
    public static function isTargetRequired(): bool
    {
        return false;
    }

    public static function isValueRequired(): bool
    {
        return false;
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $driver->switchTo()->alert()->dismiss();
    }
}
