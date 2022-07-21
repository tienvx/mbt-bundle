<?php

namespace Tienvx\Bundle\MbtBundle\Command\Window;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class SelectFrameCommand extends AbstractWindowCommand
{
    public static function isTargetRequired(): bool
    {
        return true;
    }

    public static function getTargetHelper(): string
    {
        return 'Frame locator e.g. relative=top , relative=parent , index=123 or element locator';
    }

    public static function validateTarget(?string $target): bool
    {
        return $target && static::isValidFrame($target);
    }

    protected static function isValidFrame(string $target): bool
    {
        return $target && (
            in_array($target, ['relative=top', 'relative=parent'])
                || str_starts_with($target, 'index=')
                || static::isValidSelector($target)
        );
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $targetLocator = $driver->switchTo();
        if ('relative=top' === $target) {
            $targetLocator->defaultContent();
        } elseif ('relative=parent' === $target) {
            $targetLocator->parent();
        } elseif (str_starts_with($target, 'index=')) {
            $targetLocator->frame((int) substr($target, 6));
        } else {
            $webDriverBy = $this->getSelector($target);
            $driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated($webDriverBy)
            );
            $targetLocator->frame($driver->findElement($webDriverBy));
        }
    }
}
