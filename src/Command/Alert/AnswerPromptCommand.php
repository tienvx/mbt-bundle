<?php

namespace Tienvx\Bundle\MbtBundle\Command\Alert;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class AnswerPromptCommand extends AbstractAlertCommand
{
    public static function isTargetRequired(): bool
    {
        return true;
    }

    public static function isValueRequired(): bool
    {
        return false;
    }

    public static function validateTarget(?string $target): bool
    {
        return !is_null($target);
    }

    public static function getTargetHelper(): string
    {
        return "Propt's answer";
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $alert = $driver->switchTo()->alert();
        $alert->sendKeys($target);
        $alert->accept();
    }
}
