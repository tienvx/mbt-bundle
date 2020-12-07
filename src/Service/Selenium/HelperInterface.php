<?php

namespace Tienvx\Bundle\MbtBundle\Service\Selenium;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;

interface HelperInterface
{
    public function getSelector(string $target): WebDriverBy;

    public function getDimension(string $target): WebDriverDimension;

    public function assert(bool $assertion, string $message): void;
}
