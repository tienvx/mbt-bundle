<?php

namespace Tienvx\Bundle\MbtBundle\Selenium;

use Exception;

trait Assertion
{
    protected function assert(bool $assertion, string $message): void
    {
        if (!$assertion) {
            throw new Exception($message);
        }
    }
}
