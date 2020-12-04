<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model;

interface CommandInterface
{
    // Assertions.
    public const ASSERT_ALERT = 'assertAlert';
    public const ASSERT_TEXT = 'assertText';
    public const ASSERT_EDITABLE = 'assertEditable';

    // Actions.
    public const CLICK = 'click';
    public const TYPE = 'type';
    public const CLEAR = 'clear';
    public const SET_WINDOW_SIZE = 'setWindowSize';
    public const OPEN = 'open';

    public const ALL_COMMANDS = [
        self::ASSERT_ALERT,
        self::ASSERT_TEXT,
        self::ASSERT_EDITABLE,
        self::CLICK,
        self::TYPE,
        self::CLEAR,
        self::SET_WINDOW_SIZE,
        self::OPEN,
    ];

    public const ALL_ASSERTIONS = [
        self::ASSERT_ALERT,
        self::ASSERT_TEXT,
        self::ASSERT_EDITABLE,
    ];

    public const ALL_ACTIONS = [
        self::CLICK,
        self::TYPE,
        self::CLEAR,
        self::SET_WINDOW_SIZE,
        self::OPEN,
    ];

    public function getCommand(): string;

    public function setCommand(string $command): void;

    public function getTarget(): string;

    public function setTarget(string $target): void;

    public function getValue(): ?string;

    public function setValue(?string $value): void;

    public function isSame(self $command): bool;
}
