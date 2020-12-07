<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;
use Tienvx\Bundle\MbtBundle\Service\Selenium\Helper;

class CommandRunner implements CommandRunnerInterface
{
    protected Helper $helper;

    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    public function run(CommandInterface $command, RemoteWebDriver $driver): void
    {
        switch ($command->getCommand()) {
            case CommandInterface::CLICK:
                $webDriverBy = $this->helper->getSelector($command->getTarget());
                $driver->findElement($webDriverBy)->click();
                break;
            case CommandInterface::OPEN:
                $driver->get($command->getTarget());
                break;
            case CommandInterface::SET_WINDOW_SIZE:
                $driver->manage()->window()->setSize($this->helper->getDimension($command->getTarget()));
                break;
            case CommandInterface::TYPE:
                $webDriverBy = $this->helper->getSelector($command->getTarget());
                $driver->findElement($webDriverBy)->sendKeys($command->getValue());
                break;
            case CommandInterface::CLEAR:
                $webDriverBy = $this->helper->getSelector($command->getTarget());
                $driver->findElement($webDriverBy)->clear();
                break;
            case CommandInterface::ASSERT_ALERT:
                $this->helper->assert(
                    $driver->switchTo()->alert()->getText() === $command->getTarget(),
                    sprintf('Alert is not equal to "%s"', $command->getTarget())
                );
                break;
            case CommandInterface::ASSERT_TEXT:
                $webDriverBy = $this->helper->getSelector($command->getTarget());
                $this->helper->assert(
                    $driver->findElement($webDriverBy)->getText() === $command->getValue(),
                    sprintf('Element "%s" does not have text "%s"', $command->getTarget(), $command->getValue())
                );
                break;
            case CommandInterface::ASSERT_EDITABLE:
                $webDriverBy = $this->helper->getSelector($command->getTarget());
                $element = $driver->findElement($webDriverBy);
                $this->helper->assert(
                    $element->isEnabled() && null === $element->getAttribute('readonly'),
                    sprintf('Element "%s" is not editable', $command->getTarget())
                );
                break;
        }
    }
}
