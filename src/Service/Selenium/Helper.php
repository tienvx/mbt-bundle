<?php

namespace Tienvx\Bundle\MbtBundle\Service\Selenium;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;

class Helper implements HelperInterface
{
    public function getSelector(string $target): WebDriverBy
    {
        $match = preg_match('/^(id|name|linkText|partialLinkText|css|xpath)=(.*)/i', $target, $matches);
        if (!$match) {
            throw new UnexpectedValueException('Invalid target');
        }

        list(, $mechanism, $value) = $matches;
        switch ($mechanism) {
            case 'id':
                return WebDriverBy::id($value);
            case 'name':
                return WebDriverBy::name($value);
            case 'linkText':
                return WebDriverBy::linkText($value);
            case 'partialLinkText':
                return WebDriverBy::partialLinkText($value);
            case 'css':
                return WebDriverBy::cssSelector($value);
            case 'xpath':
                return WebDriverBy::xpath($value);
        }

        throw new UnexpectedValueException('Invalid target mechanism');
    }

    public function getDimension(string $target): WebDriverDimension
    {
        $match = preg_match('/^(\d+)x(\d+)/i', $target, $matches);
        if (!$match) {
            throw new UnexpectedValueException('Invalid dimension');
        }

        list(, $width, $height) = $matches;

        return new WebDriverDimension($width, $height);
    }

    public function assert(bool $assertion, string $message): void
    {
        if (!$assertion) {
            throw new Exception($message);
        }
    }
}
