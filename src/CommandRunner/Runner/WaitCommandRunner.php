<?php

namespace Tienvx\Bundle\MbtBundle\CommandRunner\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

class WaitCommandRunner extends CommandRunner
{
    public const WAIT_FOR_ELEMENT_EDITABLE = 'waitForElementEditable';
    public const WAIT_FOR_ELEMENT_NOT_EDITABLE = 'waitForElementNotEditable';
    public const WAIT_FOR_ELEMENT_PRESENT = 'waitForElementPresent';
    public const WAIT_FOR_ELEMENT_NOT_PRESENT = 'waitForElementNotPresent';
    public const WAIT_FOR_ELEMENT_VISIBLE = 'waitForElementVisible';
    public const WAIT_FOR_ELEMENT_NOT_VISIBLE = 'waitForElementNotVisible';

    public const ALL_COMMANDS = [
        self::WAIT_FOR_ELEMENT_EDITABLE,
        self::WAIT_FOR_ELEMENT_NOT_EDITABLE,
        self::WAIT_FOR_ELEMENT_PRESENT,
        self::WAIT_FOR_ELEMENT_NOT_PRESENT,
        self::WAIT_FOR_ELEMENT_VISIBLE,
        self::WAIT_FOR_ELEMENT_NOT_VISIBLE,
    ];

    public function getActions(): array
    {
        return [
            'Wait For Element Editable' => self::WAIT_FOR_ELEMENT_EDITABLE,
            'Wait For Element Not Editable' => self::WAIT_FOR_ELEMENT_NOT_EDITABLE,
            'Wait For Element Present' => self::WAIT_FOR_ELEMENT_PRESENT,
            'Wait For Element Not Present' => self::WAIT_FOR_ELEMENT_NOT_PRESENT,
            'Wait For Element Visible' => self::WAIT_FOR_ELEMENT_VISIBLE,
            'Wait For Element Not Visible' => self::WAIT_FOR_ELEMENT_NOT_VISIBLE,
        ];
    }

    public function run(CommandInterface $command, RemoteWebDriver $driver): void
    {
        switch ($command->getCommand()) {
            case self::WAIT_FOR_ELEMENT_EDITABLE:
                $driver->wait((int) $command->getValue())->until(
                    fn () => $this->isElementEditable(
                        $driver,
                        $driver->findElement($this->getSelector($command->getTarget()))
                    ),
                    'Timed out waiting for element to be editable'
                );
                break;
            case self::WAIT_FOR_ELEMENT_NOT_EDITABLE:
                $driver->wait((int) $command->getValue())->until(
                    fn () => !$this->isElementEditable(
                        $driver,
                        $driver->findElement($this->getSelector($command->getTarget()))
                    ),
                    'Timed out waiting for element to not be editable'
                );
                break;
            case self::WAIT_FOR_ELEMENT_PRESENT:
                $driver->wait((int) $command->getValue())->until(
                    WebDriverExpectedCondition::presenceOfElementLocated($this->getSelector($command->getTarget()))
                );
                break;
            case self::WAIT_FOR_ELEMENT_NOT_PRESENT:
                $elements = $driver->findElements($this->getSelector($command->getTarget()));
                if (count($elements) > 0) {
                    $driver->wait((int) $command->getValue())->until(
                        WebDriverExpectedCondition::stalenessOf($elements[0])
                    );
                }
                break;
            case self::WAIT_FOR_ELEMENT_VISIBLE:
                $driver->wait((int) $command->getValue())->until(
                    WebDriverExpectedCondition::visibilityOfElementLocated($this->getSelector($command->getTarget()))
                );
                break;
            case self::WAIT_FOR_ELEMENT_NOT_VISIBLE:
                $driver->wait((int) $command->getValue())->until(
                    WebDriverExpectedCondition::invisibilityOfElementLocated($this->getSelector($command->getTarget()))
                );
                break;
        }
    }
}
