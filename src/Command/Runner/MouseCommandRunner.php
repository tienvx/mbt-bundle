<?php

namespace Tienvx\Bundle\MbtBundle\Command\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverPoint;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

class MouseCommandRunner extends CommandRunner
{
    public const ADD_SELECTION = 'addSelection';
    public const REMOVE_SELECTION = 'removeSelection';
    public const CHECK = 'check';
    public const UNCHECK = 'uncheck';
    public const CLICK = 'click';
    public const CLICK_AT = 'clickAt';
    public const DOUBLE_CLICK = 'doubleClick';
    public const DOUBLE_CLICK_AT = 'doubleClickAt';
    public const DRAG_AND_DROP_TO_OBJECT = 'dragAndDropToObject';
    public const MOUSE_DOWN = 'mouseDown';
    public const MOUSE_DOWN_AT = 'mouseDownAt';
    public const MOUSE_MOVE_AT = 'mouseMoveAt';
    public const MOUSE_OUT = 'mouseOut';
    public const MOUSE_OVER = 'mouseOver';
    public const MOUSE_UP = 'mouseUp';
    public const MOUSE_UP_AT = 'mouseUpAt';
    public const SELECT = 'select';

    public function getAllCommands(): array
    {
        return [
            self::ADD_SELECTION,
            self::REMOVE_SELECTION,
            self::CHECK,
            self::UNCHECK,
            self::CLICK,
            self::CLICK_AT,
            self::DOUBLE_CLICK,
            self::DOUBLE_CLICK_AT,
            self::DRAG_AND_DROP_TO_OBJECT,
            self::MOUSE_DOWN,
            self::MOUSE_DOWN_AT,
            self::MOUSE_MOVE_AT,
            self::MOUSE_OUT,
            self::MOUSE_OVER,
            self::MOUSE_UP,
            self::MOUSE_UP_AT,
            self::SELECT,
        ];
    }

    public function getCommandsRequireTarget(): array
    {
        return $this->getAllCommands();
    }

    public function getCommandsRequireValue(): array
    {
        return [
            self::ADD_SELECTION,
            self::REMOVE_SELECTION,
            self::CLICK_AT,
            self::DOUBLE_CLICK_AT,
            self::DRAG_AND_DROP_TO_OBJECT,
            self::MOUSE_DOWN_AT,
            self::MOUSE_MOVE_AT,
            self::MOUSE_UP_AT,
            self::SELECT,
        ];
    }

    public function run(CommandInterface $command, ColorInterface $color, RemoteWebDriver $driver): void
    {
        switch ($command->getCommand()) {
            case self::ADD_SELECTION:
                $select = $this->getSelect($driver->findElement($this->getSelector($command->getTarget())));
                if (str_starts_with($command->getValue(), 'index=')) {
                    $select->selectByIndex((int) substr($command->getValue(), 6));
                } elseif (str_starts_with($command->getValue(), 'value=')) {
                    $select->selectByValue(substr($command->getValue(), 6));
                } elseif (str_starts_with($command->getValue(), 'label=')) {
                    $select->selectByVisibleText(substr($command->getValue(), 6));
                }
                break;
            case self::REMOVE_SELECTION:
                $select = $this->getSelect($driver->findElement($this->getSelector($command->getTarget())));
                if (str_starts_with($command->getValue(), 'index=')) {
                    $select->deselectByIndex((int) substr($command->getValue(), 6));
                } elseif (str_starts_with($command->getValue(), 'value=')) {
                    $select->deselectByValue(substr($command->getValue(), 6));
                } elseif (str_starts_with($command->getValue(), 'label=')) {
                    $select->deselectByVisibleText(substr($command->getValue(), 6));
                }
                break;
            case self::CHECK:
                $element = $driver->findElement($this->getSelector($command->getTarget()));
                if (!$element->isSelected()) {
                    $element->click();
                }
                break;
            case self::UNCHECK:
                $element = $driver->findElement($this->getSelector($command->getTarget()));
                if ($element->isSelected()) {
                    $element->click();
                }
                break;
            case self::CLICK:
                $driver->findElement($this->getSelector($command->getTarget()))->click();
                break;
            case self::CLICK_AT:
                $point = $this->getPoint($command->getValue());
                $driver->action()->moveToElement(
                    $driver->findElement($this->getSelector($command->getTarget())),
                    $point->getX(),
                    $point->getY()
                )->click()->perform();
                break;
            case self::DOUBLE_CLICK:
                $driver->action()->doubleClick(
                    $driver->findElement($this->getSelector($command->getTarget()))
                )->perform();
                break;
            case self::DOUBLE_CLICK_AT:
                $point = $this->getPoint($command->getValue());
                $driver->action()->moveToElement(
                    $driver->findElement($this->getSelector($command->getTarget())),
                    $point->getX(),
                    $point->getY()
                )->doubleClick()->perform();
                break;
            case self::DRAG_AND_DROP_TO_OBJECT:
                $driver->action()->dragAndDrop(
                    $driver->findElement($this->getSelector($command->getTarget())),
                    $driver->findElement($this->getSelector($command->getValue()))
                )->perform();
                break;
            case self::MOUSE_DOWN:
                $driver->getMouse()->mouseDown(
                    $driver->findElement($this->getSelector($command->getTarget()))->getCoordinates()
                );
                break;
            case self::MOUSE_DOWN_AT:
                $point = $this->getPoint($command->getValue());
                $driver->getMouse()->mouseMove(
                    $driver->findElement($this->getSelector($command->getTarget()))->getCoordinates(),
                    $point->getX(),
                    $point->getY()
                )->mouseDown();
                break;
            case self::MOUSE_MOVE_AT:
                $point = $this->getPoint($command->getValue());
                $driver->getMouse()->mouseMove(
                    $driver->findElement($this->getSelector($command->getTarget()))->getCoordinates(),
                    $point->getX(),
                    $point->getY()
                );
                break;
            case self::MOUSE_OUT:
                $element = $driver->findElement($this->getSelector($command->getTarget()));
                [$rect, $vp] = $driver->executeScript(
                    // phpcs:ignore Generic.Files.LineLength
                    'return [arguments[0].getBoundingClientRect(), {height: window.innerHeight, width: window.innerWidth}];',
                    [$element]
                );
                if ($rect->top > 0) {
                    // try top
                    $y = -($rect->height / 2 + 1);
                    $driver->getMouse()->mouseMove($element->getCoordinates(), null, $y);
                    break;
                } elseif ($vp->width > $rect->right) {
                    // try right
                    $x = $rect->right / 2 + 1;
                    $driver->getMouse()->mouseMove($element->getCoordinates(), $x);
                    break;
                } elseif ($vp->height > $rect->bottom) {
                    // try bottom
                    $y = $rect->height / 2 + 1;
                    $driver->getMouse()->mouseMove($element->getCoordinates(), null, $y);
                    break;
                } elseif ($rect->left > 0) {
                    // try left
                    $x = (int) (-($rect->right / 2));
                    $driver->getMouse()->mouseMove($element->getCoordinates(), $x);
                    break;
                }

                throw new RuntimeException('Unable to perform mouse out as the element takes up the entire viewport');
            case self::MOUSE_OVER:
                $driver->getMouse()->mouseMove(
                    $driver->findElement($this->getSelector($command->getTarget()))->getCoordinates()
                );
                break;
            case self::MOUSE_UP:
                $driver->getMouse()->mouseUp(
                    $driver->findElement($this->getSelector($command->getTarget()))->getCoordinates()
                );
                break;
            case self::MOUSE_UP_AT:
                $point = $this->getPoint($command->getValue());
                $driver->getMouse()->mouseMove(
                    $driver->findElement($this->getSelector($command->getTarget()))->getCoordinates(),
                    $point->getX(),
                    $point->getY()
                )->mouseUp();
                break;
            case self::SELECT:
                $driver
                    ->findElement($this->getSelector($command->getTarget()))
                    ->findElement($this->getSelector($command->getValue()))
                    ->click();
                break;
            default:
                break;
        }
    }

    public function validateTarget(CommandInterface $command): bool
    {
        return $command->getTarget() && $this->isValidSelector($command->getTarget());
    }

    protected function getPoint(string $target): WebDriverPoint
    {
        list($x, $y) = explode(',', $target);

        return new WebDriverPoint((int) $x, (int) $y);
    }
}
