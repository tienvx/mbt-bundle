<?php

namespace Tienvx\Bundle\MbtBundle\Command\Wait;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class WaitForElementNotEditableCommand extends AbstractWaitCommand
{
    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $driver->wait((int) $value)->until(
            fn () => !$this->isElementEditable(
                $driver,
                $driver->findElement($this->getSelector($target))
            ),
            'Timed out waiting for element to not be editable'
        );
    }
}
