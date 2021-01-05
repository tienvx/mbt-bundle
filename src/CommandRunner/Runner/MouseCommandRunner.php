<?php

namespace Tienvx\Bundle\MbtBundle\CommandRunner\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverPoint;
use Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
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
    //public const MOUSE_OUT = 'mouseOut'; // Do we have to support this one?
    public const MOUSE_OVER = 'mouseOver';
    public const MOUSE_UP = 'mouseUp';
    public const MOUSE_UP_AT = 'mouseUpAt';
    public const SELECT = 'select';

    public const ALL_COMMANDS = [
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
        self::MOUSE_OVER,
        self::MOUSE_UP,
        self::MOUSE_UP_AT,
        self::SELECT,
    ];

    public function getActions(): array
    {
        return [
            'Add Selection' => self::ADD_SELECTION,
            'Remove Selection' => self::REMOVE_SELECTION,
            'Check' => self::CHECK,
            'Uncheck' => self::UNCHECK,
            'Click' => self::CLICK,
            'Click At' => self::CLICK_AT,
            'Double Click' => self::DOUBLE_CLICK,
            'Double Click At' => self::DOUBLE_CLICK_AT,
            'Drag And Drop To Object' => self::DRAG_AND_DROP_TO_OBJECT,
            'Mouse Down' => self::MOUSE_DOWN,
            'Mouse Down At' => self::MOUSE_DOWN_AT,
            'Mouse Move At' => self::MOUSE_MOVE_AT,
            'Mouse Over' => self::MOUSE_OVER,
            'Mouse Up' => self::MOUSE_UP,
            'Mouse Up At' => self::MOUSE_UP_AT,
            'Select' => self::SELECT,
        ];
    }

    public function run(CommandInterface $command, RemoteWebDriver $driver): void
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
        }
    }

    protected function getPoint(string $target): WebDriverPoint
    {
        $match = preg_match('/^(\d+),(\d+)/i', $target, $matches);
        if (!$match) {
            throw new UnexpectedValueException('Invalid point');
        }

        list(, $x, $y) = $matches;

        return new WebDriverPoint($x, $y);
    }
}
