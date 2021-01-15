<?php

namespace Tienvx\Bundle\MbtBundle\Command\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

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
        return $this->getAllCommands();
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

    public function run(CommandInterface $command, ColorInterface $color, RemoteWebDriver $driver): void
    {
        switch ($command->getCommand()) {
            case self::STORE:
                $color->setValue($command->getValue(), $command->getTarget());
                break;
            case self::STORE_ATTRIBUTE:
                list($elementLocator, $attributeName) = explode('@', $command->getTarget(), 2);
                $color->setValue(
                    $command->getValue(),
                    $driver->findElement($this->getSelector($elementLocator))->getAttribute($attributeName)
                );
                break;
            case self::STORE_ELEMENT_COUNT:
                $color->setValue(
                    $command->getValue(),
                    count($driver->findElements($this->getSelector($command->getTarget())))
                );
                break;
            case self::STORE_JSON:
                $color->setValue(
                    $command->getValue(),
                    json_decode($command->getTarget())
                );
                break;
            case self::STORE_TEXT:
                $color->setValue(
                    $command->getValue(),
                    $driver->findElement($this->getSelector($command->getTarget()))->getText()
                );
                break;
            case self::STORE_TITLE:
                $color->setValue($command->getTarget(), $driver->getTitle());
                break;
            case self::STORE_VALUE:
                $color->setValue(
                    $command->getValue(),
                    $driver->findElement($this->getSelector($command->getTarget()))->getAttribute('value')
                );
                break;
            case self::STORE_WINDOW_HANDLE:
                $color->setValue($command->getTarget(), $driver->getWindowHandle());
                break;
        }
    }
}
