<?php

namespace Tienvx\Bundle\MbtBundle\Command\Wait;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class WaitForElementNotPresentCommand extends AbstractWaitCommand
{
    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $elements = $driver->findElements($this->getSelector($target));
        if (count($elements) > 0) {
            $driver->wait((int) $value)->until(
                WebDriverExpectedCondition::stalenessOf($elements[0])
            );
        }
    }
}
