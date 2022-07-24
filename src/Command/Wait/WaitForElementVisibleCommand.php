<?php

namespace Tienvx\Bundle\MbtBundle\Command\Wait;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class WaitForElementVisibleCommand extends AbstractWaitCommand
{
    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $driver->wait((int) $value)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($this->getSelector($target))
        );
    }
}
