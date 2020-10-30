<?php

namespace Tienvx\Bundle\MbtBundle\Selenium;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;

trait DriverHelper
{
    protected function getSelector(string $target): WebDriverBy
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

    protected function getDimension(string $target): WebDriverDimension
    {
        $match = preg_match('/^(\d+)x(\d+)/i', $target, $matches);
        if (!$match) {
            throw new UnexpectedValueException('Invalid dimension');
        }

        list(, $width, $height) = $matches;

        return new WebDriverDimension($width, $height);
    }
}
