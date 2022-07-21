<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

interface CommandInterface
{
    public const MECHANISM_ID = 'id';
    public const MECHANISM_NAME = 'name';
    public const MECHANISM_LINK_TEXT = 'linkText';
    public const MECHANISM_PARTIAL_LINK_TEXT = 'partialLinkText';
    public const MECHANISM_XPATH = 'xpath';
    public const MECHANISM_CSS = 'css';
    public const MECHANISMS = [
        self::MECHANISM_ID,
        self::MECHANISM_NAME,
        self::MECHANISM_LINK_TEXT,
        self::MECHANISM_PARTIAL_LINK_TEXT,
        self::MECHANISM_XPATH,
        self::MECHANISM_CSS,
    ];

    public static function isTargetRequired(): bool;

    public static function isValueRequired(): bool;

    public static function getTargetHelper(): string;

    public static function getValueHelper(): string;

    public static function validateTarget(?string $target): bool;

    public function validateValue(?string $value): bool;

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void;
}
