<?php

namespace Tienvx\Bundle\MbtBundle\Command\Runner;

use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class WindowCommandRunner extends CommandRunner
{
    public const OPEN = 'open';
    public const SET_WINDOW_SIZE = 'setWindowSize';
    public const SELECT_WINDOW = 'selectWindow';
    public const CLOSE = 'close';
    public const SELECT_FRAME = 'selectFrame';

    public function getAllCommands(): array
    {
        return [
            self::OPEN,
            self::SET_WINDOW_SIZE,
            self::SELECT_WINDOW,
            self::CLOSE,
            self::SELECT_FRAME,
        ];
    }

    public function getCommandsRequireTarget(): array
    {
        return [
            self::OPEN,
            self::SET_WINDOW_SIZE,
            self::SELECT_WINDOW,
            self::SELECT_FRAME,
        ];
    }

    public function getCommandsRequireValue(): array
    {
        return [];
    }

    public function run(CommandInterface $command, ValuesInterface $values, RemoteWebDriver $driver): void
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
            default:
                break;
        }
    }

    public function validateTarget(CommandInterface $command): bool
    {
        switch ($command->getCommand()) {
            case self::OPEN:
                return $command->getTarget() && $this->isValidUrl($command->getTarget());
            case self::SET_WINDOW_SIZE:
                return $command->getTarget() && 2 === count(explode('x', $command->getTarget()));
            case self::SELECT_WINDOW:
                return $command->getTarget() && $this->isValidHandle($command->getTarget());
            case self::SELECT_FRAME:
                return $command->getTarget() && $this->isValidFrame($command->getTarget());
            default:
                return true;
        }
    }

    protected function getDimension(string $target): WebDriverDimension
    {
        list($width, $height) = explode('x', $target);

        return new WebDriverDimension((int) $width, (int) $height);
    }

    protected function getHandle(string $target): string
    {
        if (!$this->isValidHandle($target)) {
            throw new Exception('Invalid window handle given (e.g. handle=${handleVariable})');
        }

        return substr($target, 7);
    }

    protected function isValidHandle(string $target): bool
    {
        return str_starts_with($target, 'handle=');
    }

    protected function isValidFrame(string $target): bool
    {
        return $target && (
            in_array($target, ['relative=top', 'relative=parent'])
                || str_starts_with($target, 'index=')
                || $this->isValidSelector($target)
        );
    }

    /**
     * TODO Find a solution better than this.
     */
    protected function isValidUrl(string $target): bool
    {
        $url = filter_var($target, FILTER_SANITIZE_URL);

        return filter_var($url, FILTER_VALIDATE_URL);
    }
}
