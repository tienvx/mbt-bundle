<?php

namespace Tienvx\Bundle\MbtBundle\CommandRunner\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

class WindowCommandRunner extends CommandRunner
{
    public const OPEN = 'open';
    public const SET_WINDOW_SIZE = 'setWindowSize';
    public const SELECT_WINDOW = 'selectWindow';
    public const CLOSE = 'close';
    public const SELECT_FRAME = 'selectFrame';

    public const ALL_COMMANDS = [
        self::OPEN,
        self::SET_WINDOW_SIZE,
        self::SELECT_WINDOW,
        self::CLOSE,
        self::SELECT_FRAME,
    ];

    public function getActions(): array
    {
        return [
            'Open' => self::OPEN,
            'Set Window Size' => self::SET_WINDOW_SIZE,
            'Select Window' => self::SELECT_WINDOW,
            'Close' => self::CLOSE,
            'Select Frame' => self::SELECT_FRAME,
        ];
    }

    public function run(CommandInterface $command, RemoteWebDriver $driver): void
    {
        switch ($command->getCommand()) {
            case self::OPEN:
                $driver->get($command->getTarget());
                break;
            case self::SET_WINDOW_SIZE:
                $driver->manage()->window()->setSize($this->getDimension($command->getTarget()));
                break;
            case self::SELECT_WINDOW:
                $driver->switchTo()->window($this->getHandle($command->getTarget()));
                break;
            case self::CLOSE:
                $driver->close();
                break;
            case self::SELECT_FRAME:
                $targetLocator = $driver->switchTo();
                if ('relative=top' === $command->getTarget()) {
                    $targetLocator->defaultContent();
                } elseif ('relative=parent' === $command->getTarget()) {
                    $targetLocator->parent();
                } elseif (str_starts_with($command->getTarget(), 'index=')) {
                    $targetLocator->frame((int) substr($command->getTarget(), 6));
                } else {
                    $webDriverBy = $this->getSelector($command->getTarget());
                    $driver->wait()->until(
                        WebDriverExpectedCondition::presenceOfElementLocated($webDriverBy)
                    );
                    $targetLocator->frame($driver->findElement($webDriverBy));
                }
                break;
        }
    }

    protected function getDimension(string $target): WebDriverDimension
    {
        $match = preg_match('/^(\d+)x(\d+)/i', $target, $matches);
        if (!$match) {
            throw new UnexpectedValueException('Invalid dimension');
        }

        list(, $width, $height) = $matches;

        return new WebDriverDimension($width, $height);
    }

    protected function getHandle(string $target): string
    {
        if (!str_starts_with($target, 'handle=')) {
            throw new UnexpectedValueException('Invalid handle');
        }

        return substr($target, 7);
    }
}
