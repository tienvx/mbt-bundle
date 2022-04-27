<?php

namespace Tienvx\Bundle\MbtBundle\Command\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class StoreCommandRunner extends CommandRunner
{
    public const STORE = 'store';
    public const STORE_ATTRIBUTE = 'storeAttribute';
    public const STORE_ELEMENT_COUNT = 'storeElementCount';
    public const STORE_JSON = 'storeJson';
    public const STORE_TEXT = 'storeText';
    public const STORE_TITLE = 'storeTitle';
    public const STORE_VALUE = 'storeValue';
    public const STORE_WINDOW_HANDLE = 'storeWindowHandle';

    public function getAllCommands(): array
    {
        return [
            self::STORE,
            self::STORE_ATTRIBUTE,
            self::STORE_ELEMENT_COUNT,
            self::STORE_JSON,
            self::STORE_TEXT,
            self::STORE_TITLE,
            self::STORE_VALUE,
            self::STORE_WINDOW_HANDLE,
        ];
    }

    public function getCommandsRequireTarget(): array
    {
        return [
            self::STORE_ATTRIBUTE,
            self::STORE_ELEMENT_COUNT,
            self::STORE_JSON,
            self::STORE_TEXT,
            self::STORE_TITLE,
            self::STORE_VALUE,
            self::STORE_WINDOW_HANDLE,
        ];
    }

    public function getCommandsRequireValue(): array
    {
        return [
            self::STORE,
            self::STORE_ATTRIBUTE,
            self::STORE_ELEMENT_COUNT,
            self::STORE_JSON,
            self::STORE_TEXT,
            self::STORE_TITLE,
            self::STORE_VALUE,
        ];
    }

    public function run(CommandInterface $command, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        switch ($command->getCommand()) {
            case self::STORE:
                $values->setValue($command->getValue(), $command->getTarget());
                break;
            case self::STORE_ATTRIBUTE:
                list($elementLocator, $attributeName) = explode('@', $command->getTarget(), 2);
                $values->setValue(
                    $command->getValue(),
                    $driver->findElement($this->getSelector($elementLocator))->getAttribute($attributeName)
                );
                break;
            case self::STORE_ELEMENT_COUNT:
                $values->setValue(
                    $command->getValue(),
                    count($driver->findElements($this->getSelector($command->getTarget())))
                );
                break;
            case self::STORE_JSON:
                $values->setValue(
                    $command->getValue(),
                    json_decode($command->getTarget())
                );
                break;
            case self::STORE_TEXT:
                $values->setValue(
                    $command->getValue(),
                    $driver->findElement($this->getSelector($command->getTarget()))->getText()
                );
                break;
            case self::STORE_TITLE:
                $values->setValue($command->getTarget(), $driver->getTitle());
                break;
            case self::STORE_VALUE:
                $values->setValue(
                    $command->getValue(),
                    $driver->findElement($this->getSelector($command->getTarget()))->getAttribute('value')
                );
                break;
            case self::STORE_WINDOW_HANDLE:
                $values->setValue($command->getTarget(), $driver->getWindowHandle());
                break;
            default:
                break;
        }
    }

    public function validateTarget(CommandInterface $command): bool
    {
        switch ($command->getCommand()) {
            case self::STORE_ATTRIBUTE:
                return $command->getTarget() && $this->isValidAttribute($command->getTarget());
            case self::STORE_ELEMENT_COUNT:
            case self::STORE_TEXT:
            case self::STORE_VALUE:
                return $command->getTarget() && $this->isValidSelector($command->getTarget());
            case self::STORE_JSON:
                return $command->getTarget() && $this->isValidJson($command->getTarget());
            default:
                return true;
        }
    }

    protected function isValidAttribute(string $target): bool
    {
        list($elementLocator) = explode('@', $target, 2);

        return $this->isValidSelector($elementLocator);
    }

    protected function isValidJson(string $target): bool
    {
        json_decode($target);

        return JSON_ERROR_NONE === json_last_error();
    }
}
