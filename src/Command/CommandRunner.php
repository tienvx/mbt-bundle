<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Facebook\WebDriver\Exception\UnexpectedTagNameException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverSelect;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

abstract class CommandRunner implements CommandRunnerInterface
{
    public function supports(CommandInterface $command): bool
    {
        return in_array($command->getCommand(), $this->getAllCommands());
    }

    protected function getSelector(string $target): WebDriverBy
    {
        list($mechanism, $value) = explode('=', $target, 2);
        switch ($mechanism) {
            case 'id':
            case 'name':
            case 'linkText':
            case 'partialLinkText':
            case 'xpath':
                return WebDriverBy::{$mechanism}($value);
            case 'css':
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
}
