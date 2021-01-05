<?php

namespace Tienvx\Bundle\MbtBundle\CommandRunner\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

class KeyboardCommandRunner extends CommandRunner
{
    public const TYPE = 'type';
    public const SEND_KEYS = 'sendKeys';

    public const ALL_COMMANDS = [
        self::TYPE,
        self::SEND_KEYS,
    ];

    public function getActions(): array
    {
        return [
            'Type' => self::TYPE,
            'Send Keys' => self::SEND_KEYS,
        ];
    }

    public function run(CommandInterface $command, RemoteWebDriver $driver): void
    {
        switch ($command->getCommand()) {
            case self::TYPE:
                $driver
                    ->findElement($this->getSelector($command->getTarget()))
                    ->click()
                    ->clear()
                    ->sendKeys($this->sanitizeValue($command));
                break;
            case self::SEND_KEYS:
                $driver
                    ->findElement($this->getSelector($command->getTarget()))
                    ->click()
                    ->sendKeys($this->sanitizeValue($command));
                break;
        }
    }

    /**
     * Don't allow to upload local file.
     */
    protected function sanitizeValue(CommandInterface $command): array
    {
        return [(string) $command->getValue()];
    }
}
