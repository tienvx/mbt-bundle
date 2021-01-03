<?php

namespace Tienvx\Bundle\MbtBundle\CommandRunner\Runner;

use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

class AssertionRunner extends CommandRunner
{
    // Assertions.
    public const ASSERT_ALERT = 'assertAlert';
    public const ASSERT_TEXT = 'assertText';
    public const ASSERT_EDITABLE = 'assertEditable';

    public const ALL_COMMANDS = [
        self::ASSERT_ALERT,
        self::ASSERT_TEXT,
        self::ASSERT_EDITABLE,
    ];

    public function getActions(): array
    {
        return [];
    }

    public function getAssertions(): array
    {
        return [
            'Assert Alert' => self::ASSERT_ALERT,
            'Assert Text' => self::ASSERT_TEXT,
            'Assert Editable' => self::ASSERT_EDITABLE,
        ];
    }

    public function run(CommandInterface $command, RemoteWebDriver $driver): void
    {
        switch ($command->getCommand()) {
            case self::ASSERT_ALERT:
                $this->assert(
                    $driver->switchTo()->alert()->getText() === $command->getTarget(),
                    sprintf('Alert is not equal to "%s"', $command->getTarget())
                );
                break;
            case self::ASSERT_TEXT:
                $webDriverBy = $this->getSelector($command->getTarget());
                $this->assert(
                    $driver->findElement($webDriverBy)->getText() === $command->getValue(),
                    sprintf('Element "%s" does not have text "%s"', $command->getTarget(), $command->getValue())
                );
                break;
            case self::ASSERT_EDITABLE:
                $webDriverBy = $this->getSelector($command->getTarget());
                $element = $driver->findElement($webDriverBy);
                $this->assert(
                    $element->isEnabled() && null === $element->getAttribute('readonly'),
                    sprintf('Element "%s" is not editable', $command->getTarget())
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
