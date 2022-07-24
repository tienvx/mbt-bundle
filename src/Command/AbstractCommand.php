<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverSelect;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

abstract class AbstractCommand implements CommandInterface
{
    public static function getValueHelper(): string
    {
        return '';
    }

    public static function getTargetHelper(): string
    {
        return "Locator e.g. 'id=email' or 'css=.last-name'";
    }

    public static function validateTarget(?string $target): bool
    {
        return $target && static::isValidSelector($target);
    }

    protected static function isValidSelector(string $target): bool
    {
        list($mechanism) = explode('=', $target, 2);

        return in_array($mechanism, static::MECHANISMS);
    }

    public function validateValue(?string $value): bool
    {
        return !static::isValueRequired() || !is_null($value);
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        if (static::isTargetRequired() && empty($target)) {
            throw new UnexpectedValueException('Target is required');
        }
        if (static::isValueRequired() && empty($value)) {
            throw new UnexpectedValueException('Value is required');
        }
    }

    protected function getSelector(string $target): WebDriverBy
    {
        list($mechanism, $value) = explode('=', $target, 2);
        switch ($mechanism) {
            case static::MECHANISM_ID:
            case static::MECHANISM_NAME:
            case static::MECHANISM_LINK_TEXT:
            case static::MECHANISM_PARTIAL_LINK_TEXT:
            case static::MECHANISM_XPATH:
                return WebDriverBy::$mechanism($value);
            case static::MECHANISM_CSS:
                return WebDriverBy::cssSelector($value);
            default:
                throw new UnexpectedValueException('Invalid target mechanism');
        }
    }

    protected function isElementEditable(RemoteWebDriver $driver, WebDriverElement $element): bool
    {
        $result = $driver->executeScript(
            'return { enabled: !arguments[0].disabled, readonly: arguments[0].readOnly };',
            [$element]
        );

        return $result->enabled && !$result->readonly;
    }

    /**
     * @throws UnexpectedTagNameException
     */
    protected function getSelect(WebDriverElement $element): WebDriverSelect
    {
        return new WebDriverSelect($element);
    }

    protected function assert(bool $assertion, string $message): void
    {
        if (!$assertion) {
            throw new Exception($message);
        }
    }
}
