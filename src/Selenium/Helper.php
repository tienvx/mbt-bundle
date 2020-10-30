<?php

namespace Tienvx\Bundle\MbtBundle\Selenium;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Constant\Actions;
use Tienvx\Bundle\MbtBundle\Constant\Assertions;
use Tienvx\Bundle\MbtBundle\Model\Selenium\CommandInterface;

class Helper
{
    use Assertion;
    use DriverHelper;

    protected RemoteWebDriver $driver;

    public function __construct(RemoteWebDriver $driver)
    {
        $this->driver = $driver;
    }

    public function quit(): void
    {
        $this->driver->quit();
    }

    public function replay(CommandInterface $command): void
    {
        switch ($command->getCommand()) {
            case Actions::CLICK:
                $webDriverBy = $this->getSelector($command->getTarget());
                $this->driver->findElement($webDriverBy)->click();
                break;
            case Actions::OPEN:
                $this->driver->get($command->getTarget());
                break;
            case Actions::SET_WINDOW_SIZE:
                $this->driver->manage()->window()->setSize($this->getDimension($command->getTarget()));
                break;
            case Actions::TYPE:
                $webDriverBy = $this->getSelector($command->getTarget());
                $this->driver->findElement($webDriverBy)->sendKeys($command->getValue());
                break;
            case Actions::CLEAR:
                $webDriverBy = $this->getSelector($command->getTarget());
                $this->driver->findElement($webDriverBy)->clear();
                break;
            case Assertions::ASSERT_ALERT:
                $this->assert($this->driver->switchTo()->alert()->getText() === $command->getTarget(), sprintf('Alert is not equal to "%s"', $command->getTarget()));
                break;
            case Assertions::ASSERT_TEXT:
                $webDriverBy = $this->getSelector($command->getTarget());
                $this->assert($this->driver->findElement($webDriverBy)->getText() === $command->getValue(), sprintf('Element "%s" does not have text "%s"', $command->getTarget(), $command->getValue()));
                break;
            case Assertions::ASSERT_EDITABLE:
                $webDriverBy = $this->getSelector($command->getTarget());
                $element = $this->driver->findElement($webDriverBy);
                $this->assert($element->isEnabled() && null === $element->getAttribute('readonly'), sprintf('Element "%s" is not editable', $command->getTarget()));
                break;
        }
    }
}
