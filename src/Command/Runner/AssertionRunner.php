<?php

namespace Tienvx\Bundle\MbtBundle\Command\Runner;

use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

class AssertionRunner extends CommandRunner
{
    // Assertions.
    public const ASSERT_ALERT = 'assertAlert';
    public const ASSERT_CONFIRMATION = 'assertConfirmation';
    public const ASSERT_PROMPT = 'assertPrompt';
    public const ASSERT_TITLE = 'assertTitle';
    public const ASSERT_TEXT = 'assertText';
    public const ASSERT_NOT_TEXT = 'assertNotText';
    public const ASSERT_VALUE = 'assertValue';
    public const ASSERT_EDITABLE = 'assertEditable';
    public const ASSERT_NOT_EDITABLE = 'assertNotEditable';
    public const ASSERT_ELEMENT_PRESENT = 'assertElementPresent';
    public const ASSERT_ELEMENT_NOT_PRESENT = 'assertElementNotPresent';
    public const ASSERT_CHECKED = 'assertChecked';
    public const ASSERT_NOT_CHECKED = 'assertNotChecked';
    public const ASSERT_SELECTED_VALUE = 'assertSelectedValue';
    public const ASSERT_NOT_SELECTED_VALUE = 'assertNotSelectedValue';
    public const ASSERT_SELECTED_LABEL = 'assertSelectedLabel';
    public const ASSERT_NOT_SELECTED_LABEL = 'assertNotSelectedLabel';

    public function getAllCommands(): array
    {
        return [
            'Assert Alert' => self::ASSERT_ALERT,
            'Assert Confirmation' => self::ASSERT_CONFIRMATION,
            'Assert Prompt' => self::ASSERT_PROMPT,
            'Assert Title' => self::ASSERT_TITLE,
            'Assert Text' => self::ASSERT_TEXT,
            'Assert Not Text' => self::ASSERT_NOT_TEXT,
            'Assert Value' => self::ASSERT_VALUE,
            'Assert Editable' => self::ASSERT_EDITABLE,
            'Assert Not Editable' => self::ASSERT_NOT_EDITABLE,
            'Assert Element Present' => self::ASSERT_ELEMENT_PRESENT,
            'Assert Element Not Present' => self::ASSERT_ELEMENT_NOT_PRESENT,
            'Assert Checked' => self::ASSERT_CHECKED,
            'Assert Not Checked' => self::ASSERT_NOT_CHECKED,
            'Assert Selected Value' => self::ASSERT_SELECTED_VALUE,
            'Assert Not Selected Value' => self::ASSERT_NOT_SELECTED_VALUE,
            'Assert Selected Label' => self::ASSERT_SELECTED_LABEL,
            'Assert Not Selected Label' => self::ASSERT_NOT_SELECTED_LABEL,
        ];
    }

    public function getCommandsRequireTarget(): array
    {
        return array_values($this->getAllCommands());
    }

    public function getCommandsRequireValue(): array
    {
        return [
            self::ASSERT_TEXT,
            self::ASSERT_NOT_TEXT,
            self::ASSERT_VALUE,
            self::ASSERT_SELECTED_VALUE,
            self::ASSERT_NOT_SELECTED_VALUE,
            self::ASSERT_SELECTED_LABEL,
            self::ASSERT_NOT_SELECTED_LABEL,
        ];
    }

    public function run(CommandInterface $command, RemoteWebDriver $driver): void
    {
        switch ($command->getCommand()) {
            case self::ASSERT_ALERT:
            case self::ASSERT_CONFIRMATION:
            case self::ASSERT_PROMPT:
                $alertText = $driver->switchTo()->alert()->getText();
                $type = [
                    self::ASSERT_ALERT => 'alert',
                    self::ASSERT_CONFIRMATION => 'confirm',
                    self::ASSERT_PROMPT => 'prompt',
                ][$command->getCommand()];
                $this->assert(
                    $alertText === $command->getTarget(),
                    sprintf('Actual %s text "%s" did not match "%s"', $type, $command->getTarget(), $alertText)
                );
                break;
            case self::ASSERT_TITLE:
                $this->assert(
                    $driver->getTitle() === $command->getTarget(),
                    sprintf('Actual title "%s" did not match "%s"', $driver->getTitle(), $command->getTarget())
                );
                break;
            case self::ASSERT_TEXT:
                $elementText = $driver->findElement($this->getSelector($command->getTarget()))->getText();
                $this->assert(
                    $elementText === $command->getValue(),
                    sprintf('Actual text "%s" did not match "%s"', $elementText, $command->getValue())
                );
                break;
            case self::ASSERT_NOT_TEXT:
                $elementText = $driver->findElement($this->getSelector($command->getTarget()))->getText();
                $this->assert(
                    $elementText !== $command->getValue(),
                    sprintf('Actual text "%s" did match "%s"', $elementText, $command->getValue())
                );
                break;
            case self::ASSERT_VALUE:
                $elementValue = $driver->findElement($this->getSelector($command->getTarget()))->getAttribute('value');
                $this->assert(
                    $elementValue === $command->getValue(),
                    sprintf('Actual value "%s" did not match "%s"', $elementValue, $command->getValue())
                );
                break;
            case self::ASSERT_EDITABLE:
                $this->assert(
                    $this->isElementEditable($driver, $driver->findElement($this->getSelector($command->getTarget()))),
                    sprintf('Element "%s" is not editable', $command->getTarget())
                );
                break;
            case self::ASSERT_NOT_EDITABLE:
                $this->assert(
                    !$this->isElementEditable($driver, $driver->findElement($this->getSelector($command->getTarget()))),
                    sprintf('Element "%s" is editable', $command->getTarget())
                );
                break;
            case self::ASSERT_ELEMENT_PRESENT:
                $this->assert(
                    count($driver->findElements($this->getSelector($command->getTarget()))) > 0,
                    sprintf('Expected element "%s" was not found in page', $command->getTarget())
                );
                break;
            case self::ASSERT_ELEMENT_NOT_PRESENT:
                $this->assert(
                    0 === count($driver->findElements($this->getSelector($command->getTarget()))),
                    sprintf('Unexpected element "%s" was found in page', $command->getTarget())
                );
                break;
            case self::ASSERT_CHECKED:
                $this->assert(
                    $driver->findElement($this->getSelector($command->getTarget()))->isSelected(),
                    sprintf('Element "%s" is not checked, expected to be checked', $command->getTarget())
                );
                break;
            case self::ASSERT_NOT_CHECKED:
                $this->assert(
                    !$driver->findElement($this->getSelector($command->getTarget()))->isSelected(),
                    sprintf('Element "%s" is checked, expected to be unchecked', $command->getTarget())
                );
                break;
            case self::ASSERT_SELECTED_VALUE:
                $select = $this->getSelect($driver->findElement($this->getSelector($command->getTarget())));
                $elementValue = $select->getFirstSelectedOption()->getAttribute('value');
                $this->assert(
                    $elementValue === $command->getValue(),
                    sprintf('Actual value "%s" did not match "%s"', $elementValue, $command->getValue())
                );
                break;
            case self::ASSERT_NOT_SELECTED_VALUE:
                $select = $this->getSelect($driver->findElement($this->getSelector($command->getTarget())));
                $elementValue = $select->getFirstSelectedOption()->getAttribute('value');
                $this->assert(
                    $elementValue !== $command->getValue(),
                    sprintf('Actual value "%s" did match "%s"', $elementValue, $command->getValue())
                );
                break;
            case self::ASSERT_SELECTED_LABEL:
                $select = $this->getSelect($driver->findElement($this->getSelector($command->getTarget())));
                $elementLabel = $select->getFirstSelectedOption()->getText();
                $this->assert(
                    $elementLabel === $command->getValue(),
                    sprintf('Actual label "%s" did not match "%s"', $elementLabel, $command->getValue())
                );
                break;
            case self::ASSERT_NOT_SELECTED_LABEL:
                $select = $this->getSelect($driver->findElement($this->getSelector($command->getTarget())));
                $elementLabel = $select->getFirstSelectedOption()->getText();
                $this->assert(
                    $elementLabel !== $command->getValue(),
                    sprintf('Actual label "%s" did match "%s"', $elementLabel, $command->getValue())
                );
                break;
        }
    }

    protected function assert(bool $assertion, string $message): void
    {
        if (!$assertion) {
            throw new Exception($message);
        }
    }
}
